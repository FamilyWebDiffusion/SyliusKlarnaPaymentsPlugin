<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception\KlarnaRequestException;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\PayumTokenHelper;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class KlarnaPaymentMethodResolverDecorator implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decorated;

    private PaymentDataTransformer $paymentDataTransformer;

    private KlarnaPaymentsApiClientInterface $klarnaPaymentsApiClient;

    private EntityManagerInterface $orderManager;

    private PayumTokenHelper $payumTokenHelper;

    public function __construct(
        PaymentMethodsResolverInterface $decorated,
        ContainerInterface $container,
        PayumTokenHelper $payumTokenHelper,
    ) {
        $this->decorated = $decorated;
        $this->payumTokenHelper = $payumTokenHelper;

        $client = $container->get(KlarnaPaymentsApiClient::class);
        if ($client instanceof KlarnaPaymentsApiClientInterface) {
            $this->klarnaPaymentsApiClient = $client;
        }

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        if ($entityManager instanceof EntityManagerInterface) {
            $this->orderManager = $entityManager;
        }
    }

    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        $methods = $this->decorated->getSupportedMethods($subject);

        if (!$subject instanceof PaymentInterface) {
            return $methods;
        }

        /** @var OrderInterface $order */
        $order = $subject->getOrder();

        // Select the first well configured Klarna account, create a session, attach it to order and invalidate others

        $supportedMethods = [];
        $klarnaMethods = [];
        foreach ($methods as $method) {
            if (!$method instanceof PaymentMethodInterface) {
                continue;
            }
            /** @var GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $method->getGatewayConfig();

            // if it's not Klarna payment methods, we didn't modify it
            if (!Utils::isKlarnaPaymentsGateway($gatewayConfig)) {
                $supportedMethods[] = $method;

                continue;
            }
            // Keep track of where Klarna payment methods are supposed to be
            $supportedMethods[] = $method->getId();
            $klarnaMethods[] = $method;
        }

        // if there is no Klarna Payment, nothing more to do
        if ($klarnaMethods === []) {
            return $supportedMethods;
        }

        //Now check if Klarna API config are different
        $klarnaConfigs = [];
        $klarnaConfigCount = [];
        foreach ($klarnaMethods as $method) {
            $klarnaConfig = $this->getKlarnaGatewayConfig($method);
            if ($klarnaConfig === null) {
                continue;
            }
            $ApiUsername = $klarnaConfig->getApiUsername();
            //group methods by ApiUsername
            $klarnaConfigs[$ApiUsername][] = $method;
            if (!isset($klarnaConfigCount[$ApiUsername])) {
                $klarnaConfigCount[$ApiUsername] = 1;
            } else {
                ++$klarnaConfigCount[$ApiUsername];
            }
        }

        //get the list of ApiUsernames by number of methods configured
        \arsort($klarnaConfigCount);
        $apiUsernames = \array_keys($klarnaConfigCount);

        //validate config until we have valid payment to offer
        $methodsToAdd = [];
        do {
            $ApiUsername = \array_shift($apiUsernames);
            $methodList = $klarnaConfigs[$ApiUsername];

            //Initialise first config
            $config = null;
            foreach ($methodList as $klarnaMethod) {
                $gatewayConfig = $klarnaMethod->getGatewayConfig();
                if ($gatewayConfig !== null) {
                    $config = $gatewayConfig->getConfig();

                    break;
                }
            }

            if ($config === null) {
                break;
            }

            $this->klarnaPaymentsApiClient->initialize($config);
            $klarnaSession = new KlarnaPaymentsSession();
            $klarnaSessionId = $order->getKlarnaSessionId();
            if ($klarnaSessionId !== null) {
                try {
                    $this->klarnaPaymentsApiClient->updateAnonymousKlarnaPaymentSession($subject, $klarnaSessionId);
                    $klarnaSession = $this->klarnaPaymentsApiClient->getKlarnaPaymentSession($klarnaSessionId);
                } catch (KlarnaRequestException $e) {
                    // if we could not update, try to create a new session
                    $order->deleteKlarnaSessionId();

                    try {
                        $klarnaSession = $this->klarnaPaymentsApiClient->createAnonymousKlarnaPaymentSession($subject);
                    } catch (KlarnaRequestException $e) {
                    }
                }
            } else {
                try {
                    $klarnaSession = $this->klarnaPaymentsApiClient->createAnonymousKlarnaPaymentSession($subject);
                    $klarnaSessionId = $klarnaSession->getSessionId();
                } catch (KlarnaRequestException $e) {
                }
            }

            //we got a valid session \o/, now check if it's offer demanded payment method
            if ($klarnaSession->isCreated() && $this->hasPaymentMethodEnabled($klarnaSession, $methodList)) {
                // it's working, keep that one
                // filter method to keep only the ones selected
                $this->filterPaymentMethodEnabled($klarnaSession, $methodList);

                /** @var PaymentMethodInterface $syliusMethod */
                foreach ($methodList as $syliusMethod) {
                    $methodsToAdd[$syliusMethod->getId()] = $syliusMethod;
                }

                // if there is not enabled klarna Payment method, do nothing
                if (!isset($syliusMethod)) {
                    break;
                }

                //if there is not yet authorization callback registered at klarna
                if ($klarnaSession->getAuthorizationCallbackUrl() === null && $klarnaSessionId !== null) {
                    // prepare sync token for one klarna gateway (which one doesn't matter as we could only send one url / session)
                    /** @psalm-suppress PossiblyUndefinedVariable */
                    $token = $this->payumTokenHelper->createAuthorizeCallbackToken($order, $syliusMethod->getGatewayConfig()?->getGatewayName());
                    // update return url on klarna session (no return intended)
                    $url = $token->getTargetUrl();
                    $this->klarnaPaymentsApiClient->updateReturnUrlOnKlarnaPaymentSession($url, $klarnaSessionId);
                }
                // register the validated session id
                $order->setKlarnaSessionId($klarnaSession->getSessionId());
                $this->orderManager->flush();

                break;
            }
        } while (\count($apiUsernames) > 0);

        // reinsert valid klarna methods to supportedMethod array, in place
        $supportedMethods = array_map(static function ($m) use ($methodsToAdd): ?PaymentMethodInterface {
            if ($m instanceof PaymentMethodInterface) {
                return $m;
            }
            if (\array_key_exists($m, $methodsToAdd)) {
                return $methodsToAdd[$m];
            }

            return null;
        }, $supportedMethods);

        return \array_filter($supportedMethods);
    }

    public function supports(BasePaymentInterface $subject): bool
    {
        return $this->decorated->supports($subject);
    }

    private function getKlarnaGatewayConfig(PaymentMethodInterface $paymentMethod): ?KlarnaGatewayConfigInterface
    {
        try {
            $gatewayConfig = $paymentMethod->getGatewayConfig();
            if ($gatewayConfig === null) {
                return null;
            }
            $this->klarnaPaymentsApiClient->initialize($gatewayConfig->getConfig());
        } catch (\Exception $e) {
            return null;
        }

        return $this->klarnaPaymentsApiClient->getKlarnaPaymentGatewayConfig();
    }

    /**
     * @param array<PaymentMethodInterface> $methodList
     */
    private function hasPaymentMethodEnabled(KlarnaPaymentsSession $klarnaPaymentsSession, array $methodList): bool
    {
        foreach ($methodList as $method) {
            $klarnaConfig = $this->getKlarnaGatewayConfig($method);
            if ($klarnaConfig !== null &&
                \in_array($klarnaConfig->getKlarnaPaymentMethod(), $klarnaPaymentsSession->getPaymentMethodCategoriesIdentifiers(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Modify KlarnaPaymentsSession and klarnaMethodList to keep only payment methods matching in each other
     *
     * @param PaymentMethodInterface[] $methodList
     */
    private function filterPaymentMethodEnabled(KlarnaPaymentsSession $klarnaPaymentsSession, array &$methodList): void
    {
        $methodListEnabled = [];
        $methodIdentifierList = [];
        /** @var PaymentMethodInterface $method */
        foreach ($methodList as $method) {
            $klarnaConfig = $this->getKlarnaGatewayConfig($method);
            if ($klarnaConfig === null) {
                continue;
            }
            if (\in_array($klarnaConfig->getKlarnaPaymentMethod(), $klarnaPaymentsSession->getPaymentMethodCategoriesIdentifiers(), true)) {
                $methodListEnabled[] = $method;
            }
            $methodIdentifierList[] = $klarnaConfig->getKlarnaPaymentMethod();
        }

        $klarnaPaymentsSessionMethodCategories = $klarnaPaymentsSession->getPaymentMethodCategories();
        $klarnaPaymentsSessionMethodCategoriesEnabled = [];
        foreach ($klarnaPaymentsSession->getPaymentMethodCategoriesIdentifiers() as $key => $identifier) {
            if (\in_array($identifier, $methodIdentifierList, true)) {
                $klarnaPaymentsSessionMethodCategoriesEnabled[] = $klarnaPaymentsSessionMethodCategories[$key];
            }
        }

        $klarnaPaymentsSession->setPaymentMethodCategories($klarnaPaymentsSessionMethodCategoriesEnabled);
        $methodList = $methodListEnabled;
    }
}
