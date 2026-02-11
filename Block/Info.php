<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block;

use Magento\Payment\Block\Info as MagentoInfo;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Model\Order\ValidatorPool\DiscrepancyValidator;
use Worldline\PaymentCore\Model\OrderState\OrderStateHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\InfoFormatterInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\Model\Transaction\PaymentInfoBuilder;
use Worldline\PaymentCore\Api\Ui\PaymentIconsProviderInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Info extends MagentoInfo
{
    public const MAX_HEIGHT = '25px';

    /**
     * @var PaymentIconsProviderInterface
     */
    private $paymentIconProvider;

    /**
     * @var PaymentInfoBuilder
     */
    private $paymentInfoBuilder;

    /**
     * @var InfoFormatterInterface
     */
    private $infoFormatter;

    /**
     * @var PaymentInfoInterface
     */
    private $paymentInformation;

    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var array
     */
    private $paymentDetails;

    /**
     * @var array
     */
    private $splitPayment;

    /**
     * @var bool
     */
    private $isSplitPayment = false;

    /**
     * @var string
     */
    private $splitPaymentAmount;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    protected $_template = 'Worldline_PaymentCore::info/default.phtml';

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    /**
     * @var DiscrepancyValidator
     */
    private $discrepancyValidator;

    /**
     * @var CurrencyAmountNormalizer
     */
    private $currencyAmountNormalizer;

    /**
     * @var OrderStateHelper
     */
    private $orderStateHelper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    public function __construct(
        Context $context,
        PaymentIconsProviderInterface $paymentIconProvider,
        PaymentInfoBuilder $paymentInfoBuilder,
        InfoFormatterInterface $infoFormatter,
        ClientProviderInterface $clientProvider,
        WorldlineConfig $worldlineConfig,
        LoggerInterface $logger,
        Registry $registry,
        GeneralSettingsConfigInterface $generalSettings,
        DiscrepancyValidator $discrepancyValidator,
        CurrencyAmountNormalizer $currencyAmountNormalizer,
        OrderStateHelper $orderStateHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentIconProvider = $paymentIconProvider;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
        $this->infoFormatter = $infoFormatter;
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->generalSettings = $generalSettings;
        $this->discrepancyValidator = $discrepancyValidator;
        $this->currencyAmountNormalizer = $currencyAmountNormalizer;
        $this->orderStateHelper = $orderStateHelper;
    }

    public function getTransactionInfo(): array
    {
        $specificInformation = [];
        $splitPaymentInfo = $this->getSplitPaymentInformation();
        if ($splitPaymentInfo &&
            (
                $this->getPaymentInformation()->getPaymentProductId() !==
                PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID &&
                $this->getPaymentInformation()->getPaymentProductId() !==
                PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID
            )) {
            $this->isSplitPayment = true;
            $specificInformation[] = array_merge(
                $specificInformation,
                $this->infoFormatter->format($splitPaymentInfo)
            );
        }
        $paymentInformation = $this->getPaymentInformation();

        if ($this->isSplitPayment) {
            $formattedSplitPaymentAmount = $this->paymentInfoBuilder->
            getFormattedSplitPaymentAmount((int)$this->splitPaymentAmount, $paymentInformation->getCurrency());
            $paymentInformation->setAuthorizedAmount(
                $paymentInformation->getAuthorizedAmount() - $formattedSplitPaymentAmount
            );
        }
        $specificInformation[] = array_merge(
            $specificInformation,
            $this->infoFormatter->format($this->getPaymentInformation())
        );

        return $specificInformation;
    }

    /**
     * @return array|void
     */
    public function getOrderDiscrepancy()
    {
        $order = $this->registry->registry('current_order');
        if (!$order) {
            return;
        }

        $paymentInfo = $this->getPaymentInformation();
        $wlPayment = $this->discrepancyValidator->getWlPayment($order->getIncrementId());
        $currency = $wlPayment->getCurrency();
        $paymentAmount = (float)$this->currencyAmountNormalizer->normalize((float) $wlPayment->getAmount(), $currency);
        $statusCode = $paymentInfo->getStatusCode();
        $orderTotal = round((float)$order->getGrandTotal(), 2);
        $isDiscrepancyOrder = $orderTotal !== $paymentAmount;

        $discrepancyStatus = $this->generalSettings->getOrderDiscrepancyStatus();
        $discrepancyState = $this->orderStateHelper->getStateByStatus($discrepancyStatus);

        if ($isDiscrepancyOrder && $order->getState() !== $discrepancyState &&
            !$this->isOrderDiscrepancyAccepted() && !$this->isOrderDiscrepancyRefunded()) {
            $order->setState($discrepancyState)->setStatus($discrepancyStatus);
        }

        return [
            'isDiscrepancyOrder' => $isDiscrepancyOrder,
            'orderTotal' => $orderTotal,
            'currency' => $order->getOrderCurrency()->getCurrencySymbol(),
            'paymentAmount' => $paymentAmount,
            'statusCode' => $statusCode,
            'transactionId' => $paymentInfo->getLastTransactionNumber(),
            'currencyName' => $currency
        ];
    }

    /**
     * @return bool
     */
    public function isOrderDiscrepancyAccepted(): bool
    {
        $order = $this->registry->registry('current_order');
        if (!$order) {
            return false;
        }

        return (bool)$order->getData('discrepancy_accepted');
    }

    /**
     * @return bool
     */
    public function isOrderDiscrepancyRefunded(): bool
    {
        $order = $this->registry->registry('current_order');
        if (!$order) {
            return false;
        }

        return (bool)$order->getData('discrepancy_rejected');
    }

    /**
     * @return mixed
     */
    public function getCurrentOrderId()
    {
        $order = $this->registry->registry('current_order');

        return $order ? $order->getId() : '';
    }

    public function getPaymentTitle(array $paymentInformation = []): string
    {
        $methodUsed = __('Payment');
        $paymentProductId = array_key_exists('paymentProductId', $paymentInformation)
            ? $paymentInformation['paymentProductId'] :
            $this->getPaymentInformation()->getPaymentProductId();
        if ($paymentProductId
            && !empty(PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$paymentProductId]['label'])
        ) {
            $methodUsed = PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$paymentProductId]['label'];
        }

        return __('%1 with Worldline', $methodUsed)->render();
    }

    public function getIconUrl(array $paymentInformation = []): string
    {
        return $this->getIconForType($paymentInformation)['url'] ?? '';
    }

    public function getIconWidth(array $paymentInformation = []): int
    {
        return $this->getIconForType($paymentInformation)['width'];
    }

    public function getIconHeight(array $paymentInformation = []): int
    {
        return $this->getIconForType($paymentInformation)['height'];
    }

    public function getIconTitle(array $paymentInformation = []): Phrase
    {
        return __($this->getIconForType($paymentInformation)['title']);
    }

    public function getAspectRatio(): string
    {
        return $this->getIconWidth() . '/' . $this->getIconHeight();
    }

    public function getMaxHeight(): string
    {
        return self::MAX_HEIGHT;
    }

    private function getIconForType(array $paymentInformation = []): array
    {
        $paymentProductId = array_key_exists('paymentProductId', $paymentInformation) ?
            $paymentInformation['paymentProductId'] :
            $this->getPaymentInformation()->getPaymentProductId();

        $storeId = (int)$this->getInfo()->getOrder()->getStoreId();
        return $this->paymentIconProvider->getIconById($paymentProductId, $storeId);
    }

    public function getPaymentInformation(): PaymentInfoInterface
    {
        if (null === $this->paymentInformation) {
            $this->paymentInformation = $this->paymentInfoBuilder->build($this->getInfo()->getOrder());
        }

        return $this->paymentInformation;
    }

    /**
     * @return PaymentInfoInterface|null
     */
    public function getSplitPaymentInformation(): ?PaymentInfoInterface
    {
        $storeId = (int)$this->getInfo()->getOrder()->getStoreId();

        try {
            $this->loadPaymentDetails($storeId);

            $payment = $this->findSplitPayment($storeId);
            if (!$payment) {
                return null;
            }

            $this->setSplitPaymentFinalStatus($payment);
            $this->calculateSplitPaymentAmount($payment);

            return $this->paymentInfoBuilder->buildSplitTransaction(
                $payment,
                (int) $this->splitPaymentAmount
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return null;
        }
    }

    /**
     * Load payment details if not already loaded.
     *
     * @param int $storeId
     *
     * @return void
     */
    private function loadPaymentDetails(int $storeId): void
    {
        if ($this->paymentDetails !== null) {
            return;
        }

        $order = $this->getInfo()->getOrder();
        $paymentId = $this->paymentInfoBuilder->getPaymentByOrderId($order);

        $this->paymentDetails = $this->clientProvider->getClient($storeId)
            ->merchant($this->worldlineConfig->getMerchantId($storeId))
            ->payments()
            ->getPaymentDetails($paymentId);
    }

    /**
     * Find the split payment with a specific product ID.
     *
     * @param int $storeId
     *
     * @return object|mixed|null
     */
    private function findSplitPayment(int $storeId): ?object
    {
        $this->splitPayment = ['payment' => null];

        foreach ($this->paymentDetails->getOperations() as $paymentDetail) {
            $payment = $this->safeGetPayment($storeId, $paymentDetail->getId());
            if (!$payment) {
                continue;
            }

            $productId = $this->getPaymentProductId($payment);
            if ($this->isSplitPaymentProduct($productId)) {
                $this->splitPayment['payment'] = $payment;
                break;
            }
        }

        return $this->splitPayment['payment'];
    }

    /**
     * Safely fetch a payment and log any exceptions.
     *
     * @param int $storeId
     * @param string $paymentId
     *
     * @return object|null
     */
    private function safeGetPayment(int $storeId, string $paymentId): ?object
    {
        try {
            return $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->getPayment($paymentId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }

    /**
     * Extract the product ID from a payment.
     */
    private function getPaymentProductId($payment): ?int
    {
        $output = $this->getOutput($payment);
        $redirectOutput = $output ? $output->getRedirectPaymentMethodSpecificOutput() : null;
        return $redirectOutput ? $redirectOutput->getPaymentProductId() : null;
    }

    /**
     * Check if a product ID is one of the split payment product types.
     */
    private function isSplitPaymentProduct(?int $productId): bool
    {
        return in_array($productId, [
            PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID,
            PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID,
        ], true);
    }

    /**
     * Calculate the split payment amount difference.
     */
    private function calculateSplitPaymentAmount($payment): void
    {
        $lastOperation = end($this->paymentDetails->getOperations());
        $this->splitPaymentAmount =
            $payment->getPaymentOutput()->getAmountOfMoney()->getAmount() -
            $lastOperation->getAmountOfMoney()->getAmount();
    }

    public function getMethod(): MethodInterface
    {
        return $this->getInfo()->getOrder()->getPayment()->getMethodInstance();
    }

    public function toPdf(): string
    {
        $this->setTemplate('Worldline_PaymentCore::info/pdf/worldline_payment.phtml');
        return $this->toHtml();
    }

    /**
     * @param $payment
     *
     * @return void
     */
    private function setSplitPaymentFinalStatus(&$payment)
    {
        $payment->getStatusOutput()->setStatusCode($this->paymentDetails->getStatusOutput()->getStatusCode());
        $payment->getStatusOutput()->setStatusCategory($this->paymentDetails->getStatusOutput()->getStatusCategory());
        $payment->setStatus($this->paymentDetails->getStatusOutput()->getStatusCategory());
    }

    /**
     * @param DataObject $response
     *
     * @return DataObject
     */
    private function getOutput(DataObject $response): DataObject
    {
        $output = null;
        if ($response instanceof PaymentResponse || $response instanceof PaymentDetailsResponse) {
            $output = $response->getPaymentOutput();
        }

        if ($response instanceof RefundResponse) {
            $output = $response->getRefundOutput();
        }

        if (!$output) {
            throw new LocalizedException(__('Invalid output model'));
        }

        return $output;
    }
}
