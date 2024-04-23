<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\HttpClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\ShippingInfoDataTransformerInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception\KlarnaRequestException;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentGatewayConfig;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Core\Model\PaymentInterface;

final class KlarnaPaymentsApiClient implements KlarnaPaymentsApiClientInterface
{
    private HttpClientInterface $client;

    private PaymentDataTransformer $paymentDataTransformer;

    private ShippingInfoDataTransformerInterface $shippingInfoDataTransformer;

    /** @var array<string, string|array> */
    private array $initialConfig;

    private ArrayObject $config;

    private KlarnaGatewayConfigInterface $gatewayConfig;

    /**
     * @param array<string, string|array> $initialConfig
     */
    public function __construct(
        HttpClientInterface $client,
        PaymentDataTransformer $paymentDataTransformer,
        ShippingInfoDataTransformerInterface $shippingInfoDataTransformer,
        array $initialConfig,
    ) {
        $this->client = $client;
        $this->paymentDataTransformer = $paymentDataTransformer;
        $this->shippingInfoDataTransformer = $shippingInfoDataTransformer;
        $this->initialConfig = $initialConfig;
    }

    /**
     * @param array<string, string|array> $config
     */
    public function initialize(array $config): void
    {
        $this->config = new ArrayObject(\array_merge($this->initialConfig, $config));
        $this->gatewayConfig = new KlarnaPaymentGatewayConfig($this->config);
    }

    public function getKlarnaPaymentGatewayConfig(): KlarnaGatewayConfigInterface
    {
        return $this->gatewayConfig;
    }

    public function getKlarnaPaymentsApiUrl(): string
    {
        return $this->gatewayConfig->getKlarnaPaymentsApiUrl();
    }

    public function getKlarnaOrderManagementApiUrl(): string
    {
        return $this->gatewayConfig->getKlarnaOrderManagementApiUrl();
    }

    /**
     * @throws KlarnaRequestException
     */
    public function createAnonymousKlarnaPaymentSession(PaymentInterface $payment): KlarnaPaymentsSession
    {
        $data = $this->paymentDataTransformer->transformAnonymized([], $payment);
        $url = $this->getKlarnaPaymentsApiUrl() . 'sessions';
        /** @var array<string, array|bool|string> $return */
        $return = $this->requestClient('POST', $url, $data);
        $success = (bool) $return['success'];

        if (!$success) {
            throw new KlarnaRequestException('Impossible to create Klarna Session', $url);
        }

        $klarnaPaymentsSession = new KlarnaPaymentsSession();
        $this->setKlarnaPaymentSessionData($klarnaPaymentsSession, $return);
        if (is_string($return['session_id'])) {
            $klarnaPaymentsSession->setSessionId($return['session_id']);
        }

        return $klarnaPaymentsSession;
    }

    /**
     * @throws KlarnaRequestException
     */
    public function updateAnonymousKlarnaPaymentSession(PaymentInterface $payment, string $klarnaPaymentsSessionId): void
    {
        $data = $this->paymentDataTransformer->transformAnonymized([], $payment);

        $url = $this->getKlarnaPaymentsApiUrl() . 'sessions/' . $klarnaPaymentsSessionId;
        $return = $this->requestClient('POST', $url, $data);
        $success = (bool) $return['success'];

        if (!$success) {
            throw new KlarnaRequestException('Impossible to update Klarna Session', $url);
        }
    }

    public function updateReturnUrlOnKlarnaPaymentSession(string $targetUrl, string $klarnaPaymentsSessionId): void
    {
        $data = ['merchant_urls' => ['authorization' => $targetUrl]];

        $url = $this->getKlarnaPaymentsApiUrl() . 'sessions/' . $klarnaPaymentsSessionId;
        $this->requestClient('POST', $url, $data);
    }

    /**
     * @throws KlarnaRequestException
     */
    public function getKlarnaPaymentSession(string $klarnaPaymentsSessionId): KlarnaPaymentsSession
    {
        $url = $this->getKlarnaPaymentsApiUrl() . 'sessions/' . $klarnaPaymentsSessionId;
        $return = $this->requestClient('GET', $url);
        $success = (bool) $return['success'];

        if (!$success) {
            throw new KlarnaRequestException('Impossible to get Klarna Session', $url);
        }

        $klarnaPaymentsSession = new KlarnaPaymentsSession();

        //If session is complete, return empty one
        if ($return['status'] !== 'complete') {
            $this->setKlarnaPaymentSessionData($klarnaPaymentsSession, $return);
            $klarnaPaymentsSession->setSessionId($klarnaPaymentsSessionId);
        }

        return $klarnaPaymentsSession;
    }

    /**
     * https://docs.klarna.com/klarna-payments/other-actions/cancel-an-authorization/
     * When a customer won't complete a purchase or you won't use the authorization token immediately, you can cancel the authorization. This action clears the customer's debt.
     */
    public function cancelKlarnaAuthorization(string $authorizationToken): bool
    {
        $return = $this->requestClient('DELETE', $this->getKlarnaPaymentsApiUrl() . 'authorizations/' . $authorizationToken);
        if (isset($return['status_code']) && $return['status_code'] === 204) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, array|bool|int|string>
     */
    public function createNewKlarnaOrder(PaymentInterface $payment, string $authorizationToken): array
    {
        $data = $this->paymentDataTransformer->transform([], $payment);
        $order = $payment->getOrder();
        if ($order !== null) {
            $data['merchant_reference1'] = $order->getNumber();
        }

        $return = $this->requestClient('POST', $this->getKlarnaPaymentsApiUrl() . 'authorizations/' . $authorizationToken . '/order', $data);
        if (isset($return['fraud_status'])) {
            if ($return['fraud_status'] === KlarnaDataInterface::FRAUD_STATUS_ACCEPTED) {
                $return['status'] = KlarnaOrderStatus::STATUS_AUTHORIZED;
            }
            if ($return['fraud_status'] === KlarnaDataInterface::FRAUD_STATUS_PENDING) {
                $return['status'] = KlarnaOrderStatus::STATUS_PENDING;
            }
        }

        return $return;
    }

    /**
     * @return array<string, array|bool|int|string>
     */
    public function cancelKlarnaOrder(PaymentInterface $payment): array
    {
        $details = $payment->getDetails();

        $return = $this->requestClient('POST', $this->getKlarnaOrderManagementApiUrl() . $details['order_id'] . '/cancel');
        $return['status'] = KlarnaOrderStatus::STATUS_CANCEL;

        return $return;
    }

    /**
     * @return array<string, array|bool|int|string>
     */
    public function captureAllApprovedKlarnaOrder(PaymentInterface $payment): array
    {
        $details = $payment->getDetails();
        $data = $this->shippingInfoDataTransformer->transform($payment);
        $data['captured_amount'] = $payment->getAmount();

        $return = $this->requestClient('POST', $this->getKlarnaOrderManagementApiUrl() . $details['order_id'] . '/captures', $data);
        $success = (bool) $return['success'];

        if ($success) {
            $return['status'] = KlarnaOrderStatus::STATUS_CAPTURED;
        }

        return $return;
    }

    /**
     * @return array<string, array|bool|int|string>
     */
    public function refundAllKlarnaOrder(PaymentInterface $payment): array
    {
        $details = $payment->getDetails();
        $data = [];

        $data['refunded_amount'] = $payment->getAmount();

        $return = $this->requestClient('POST', $this->getKlarnaOrderManagementApiUrl() . $details['order_id'] . '/refunds', $data);
        $return['status'] = KlarnaOrderStatus::STATUS_REFUNDED;

        return $return;
    }

    /**
     * @param array<string, array|bool|int|string> $data
     */
    private function setKlarnaPaymentSessionData(KlarnaPaymentsSession $klarnaPaymentsSession, array $data): void
    {
        $success = (bool) $data['success'];

        if ($success) {
            if (!isset($data['payment_method_categories'])) {
                $data['payment_method_categories'] = [];
            }
            if (\is_string($data['client_token'])) {
                $klarnaPaymentsSession->setClientToken($data['client_token']);
            }
            if (isset($data['merchant_urls']) &&
                \is_array($data['merchant_urls']) &&
                isset($data['merchant_urls']['authorization']) &&
                \is_string($data['merchant_urls']['authorization'])
            ) {
                $klarnaPaymentsSession->setAuthorizationCallbackUrl($data['merchant_urls']['authorization']);
            }
            if (\is_array($data['payment_method_categories'])) {
                $klarnaPaymentsSession->setPaymentMethodCategories($data['payment_method_categories']);
            }
            if (isset($data['authorization_token']) && \is_string($data['authorization_token'])) {
                $klarnaPaymentsSession->setAuthorizationToken($data['authorization_token']);
            }
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, array|bool|int|string>
     */
    private function requestClient(string $method, string $url, array $data = []): array
    {
        $options = [
            'auth_basic' => [$this->gatewayConfig->getApiUsername(), $this->gatewayConfig->getApiPassword()],
            'headers' => ['Content-Type' => 'application/json'],
        ];

        return $this->client->request($method, $url, $options, $data)
            ?? ['success' => false, 'status' => ''];
    }
}
