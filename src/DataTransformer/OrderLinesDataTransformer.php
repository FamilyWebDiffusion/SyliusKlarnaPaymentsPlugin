<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaDataInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Webmozart\Assert\Assert;

class OrderLinesDataTransformer implements DataTransformerInterface
{
    private CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function __invoke(array $data, PaymentInterface $payment): array
    {
        $order = $payment->getOrder();
        Assert::notNull($order);
        /** @var array<int,array> $orderLines */
        $orderLines = $data['order_lines'];
        $data['order_lines'] = \array_merge($orderLines, $this->getItems($order));

        return $data;
    }

    public function isAnonymous(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    private function getItems(OrderInterface $order): array
    {
        $items = $order->getItems()->getValues();
        $locale = $order->getLocaleCode() ?? 'en-US';

        return \array_filter(\array_map(function (OrderItemInterface $item) use ($locale): ?array {
            $product = $item->getProduct();
            if ($product === null) {
                return null;
            }

            /** @var ProductTranslationInterface $productTrans */
            $productTrans = $this->getTranslation($product, $locale);

            $data = [
                'name' => $productTrans->getName(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPrice(),
                'merchant_data' => $item->getId(),
                'total_discount_amount' => $item->getQuantity() * ($item->getUnitPrice() - $item->getFullDiscountedUnitPrice()),
                'total_amount' => $item->getTotal(),
            ];

            $data['type'] = KlarnaDataInterface::ORDER_LINE_TYPE_PHYSICAL;
            $variant = $item->getVariant();
            if ($variant !== null) {
                $data['reference'] = $variant->getCode();
                if (!$variant->isShippingRequired()) {
                    $data['type'] = KlarnaDataInterface::ORDER_LINE_TYPE_DIGITAL;
                }
            }

            $image = $this->getImage($item);
            if ($image === null) {
                return $data;
            }
            $imagePath = $image->getPath();
            if ($imagePath !== null) {
                $data['image_url'] = $this->cacheManager->getBrowserPath($imagePath, 'sylius_large');
            }

            return $data;
        }, $items));
    }

    private function getTranslation(TranslatableInterface $translatable, string $locale): TranslationInterface
    {
        if ($translatable->getTranslations()->offsetExists($locale)) {
            return $translatable->getTranslation($locale);
        }

        return $translatable->getTranslation();
    }

    private function getImage(OrderItemInterface $item): ?ImageInterface
    {
        $variant = $item->getVariant();
        if ($variant !== null) {
            $image = $variant->getImages()->first();
            if ($image !== false) {
                return $image;
            }
        }

        $product = $item->getProduct();
        if ($product !== null) {
            $image = $product->getImages()->first();
            if ($image !== false) {
                return $image;
            }
        }

        return null;
    }
}
