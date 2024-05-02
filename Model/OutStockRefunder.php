<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\Service\Refund\RefundRequestDataBuilderInterface;
use Worldline\PaymentCore\Model\Config\AutoRefundConfigProvider;
use Worldline\PaymentCore\Model\Order\AutoRefundAttemptNotification;
use Worldline\PaymentCore\Model\Order\AutoRefundToCustomerNotification;
use Worldline\PaymentCore\Service\Refund\CreateRefundService;

class OutStockRefunder
{
    public const OUT_STOCK_CODE = 'out_stock';
    public const MSI_OUT_STOCK_CODE = 'is_salable_with_reservations-not_enough_qty';

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    /**
     * @var CreateRefundService
     */
    private $createRefundService;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    /**
     * @var AutoRefundConfigProvider
     */
    private $autoRefundConfigProvider;

    /**
     * @var RefundRequestDataBuilderInterface
     */
    private $refundRequestDataBuilder;

    /**
     * @var AutoRefundAttemptNotification
     */
    private $autoRefundAttemptNotification;

    /**
     * @var AutoRefundToCustomerNotification
     */
    private $autoRefundToCustomerNotification;

    public function __construct(
        StockStateInterface $stockState,
        QuoteResourceInterface $quoteResource,
        CreateRefundService $createRefundService,
        PaymentIdFormatterInterface $paymentIdFormatter,
        AutoRefundConfigProvider $autoRefundConfigProvider,
        RefundRequestDataBuilderInterface $refundRequestDataBuilder,
        AutoRefundAttemptNotification $autoRefundAttemptNotification,
        AutoRefundToCustomerNotification $autoRefundToCustomerNotification
    ) {
        $this->stockState = $stockState;
        $this->quoteResource = $quoteResource;
        $this->createRefundService = $createRefundService;
        $this->paymentIdFormatter = $paymentIdFormatter;
        $this->autoRefundConfigProvider = $autoRefundConfigProvider;
        $this->refundRequestDataBuilder = $refundRequestDataBuilder;
        $this->autoRefundAttemptNotification = $autoRefundAttemptNotification;
        $this->autoRefundToCustomerNotification = $autoRefundToCustomerNotification;
    }

    public function refundTransaction(string $incrementId): void
    {
        if (!$this->autoRefundConfigProvider->isEnabled()) {
            return;
        }

        $quote = $this->quoteResource->getQuoteByReservedOrderId($incrementId);
        if (!$quote) {
            return;
        }

        if (!$this->isOutStockReason($quote)) {
            return;
        }

        $currency = $quote->getCurrency()->getQuoteCurrencyCode();
        $paymentId = (string)$quote->getPayment()->getAdditionalInformation(PaymentInterface::PAYMENT_ID);
        $paymentId = $this->paymentIdFormatter->validateAndFormat($paymentId, true);

        $refundRequest = $this->refundRequestDataBuilder->build((float)$quote->getGrandTotal(), $currency);
        $storeId = (int)$quote->getStoreId();
        $this->autoRefundAttemptNotification->notify($quote);
        $this->autoRefundToCustomerNotification->notify($quote);
        $this->createRefundService->execute($paymentId, $refundRequest, $storeId);

        $quote->setReservedOrderId(null);
        $this->quoteResource->save($quote);
    }

    private function isOutStockReason(CartInterface $quote): bool
    {
        foreach ($quote->getAllItems() as $item) {
            $result = $this->stockState->checkQuoteItemQty(
                (int)$item->getProductId(),
                $item->getQty(),
                $item->getQty(),
                $item->getQty(),
                (int)$quote->getStoreId()
            );

            if ($errorCode = $result->getErrorCode()) {
                if ($errorCode === self::OUT_STOCK_CODE || $errorCode === self::MSI_OUT_STOCK_CODE) {
                    return true;
                }
            }

            if ($result->getQuoteMessageIndex() === 'qty') {
                return true;
            }
        }

        return false;
    }
}
