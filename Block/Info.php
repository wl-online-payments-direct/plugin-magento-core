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

    private const STATUS_CODE_CAPTURED = 9;
    private const STATUS_CODE_REFUNDED = 8;
    private const STATUS_CODE_PENDING_REFUND = 81;

    private const STATUS_AMOUNT_MAP = [
        self::STATUS_CODE_CAPTURED => 'captured',
        self::STATUS_CODE_REFUNDED => 'refunded',
        self::STATUS_CODE_PENDING_REFUND => 'pending_refund',
    ];

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
    private $splitPayments = [];

    /**
     * @var bool
     */
    private $isSplitPayment = false;

    /**
     * @var object|null
     */
    private $mainPaymentResponse;

    /**
     * @var int
     */
    private $totalSplitPaymentAmountCents = 0;

    /**
     * @var array|null
     */
    private $operationAnalysis;

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
        $splitPaymentInfos = $this->getSplitPaymentsInformation();
        if (!empty($splitPaymentInfos) &&
            (
                $this->getPaymentInformation()->getPaymentProductId() !==
                PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID &&
                $this->getPaymentInformation()->getPaymentProductId() !==
                PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID
            )) {
            $this->isSplitPayment = true;
            foreach ($splitPaymentInfos as $splitPaymentInfo) {
                $specificInformation[] = array_merge(
                    $specificInformation,
                    $this->infoFormatter->format($splitPaymentInfo)
                );
            }
        }
        $paymentInformation = $this->getPaymentInformation();

        if ($this->isSplitPayment) {
            $this->applyMainPaymentDetails($paymentInformation);
            $analysis = $this->analyzeOperationsByMethod();
            $cardData = $analysis['card'] ?? null;
            if (!$cardData || $cardData['captured'] <= 0) {
                return $specificInformation;
            }
            $this->applyMainPaymentRefundData($paymentInformation);
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
        if (!$wlPayment) {
            return [];
        }

        $currency = $wlPayment->getCurrency();
        $paymentAmount = (float)$this->currencyAmountNormalizer->normalize((float) $wlPayment->getAmount(), $currency);
        $statusCode = $paymentInfo->getStatusCode();
        $orderTotal = round((float)$order->getGrandTotal(), 2);
        $isDiscrepancyOrder = ($orderTotal !== $paymentAmount) &&
            ($order->getStatus() === $this->generalSettings->getOrderDiscrepancyStatus());

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
     * @return PaymentInfoInterface[]
     */
    public function getSplitPaymentsInformation(): array
    {
        $storeId = (int)$this->getInfo()->getOrder()->getStoreId();

        try {
            $this->loadPaymentDetails($storeId);
            $splitPayments = $this->findSplitPayments($storeId);
            if (empty($splitPayments)) {
                return [];
            }

            $result = [];
            $this->totalSplitPaymentAmountCents = 0;
            foreach ($splitPayments as $splitPaymentData) {
                $payment = $splitPaymentData['payment'];
                $operationAmountCents = $splitPaymentData['operationAmount'];
                $this->totalSplitPaymentAmountCents += $operationAmountCents;

                $paymentInfo = $this->paymentInfoBuilder->buildSplitTransaction(
                    $payment,
                    $operationAmountCents
                );
                $this->applySplitRefundData($paymentInfo, $operationAmountCents);
                $result[] = $paymentInfo;
            }

            return $result;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [];
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
     * Find all split payments with specific product IDs.
     *
     * @param int $storeId
     *
     * @return array[] Each element contains 'payment' (PaymentResponse) and 'operationAmount' (int, cents)
     */
    private function findSplitPayments(int $storeId): array
    {
        $this->splitPayments = [];
        $this->mainPaymentResponse = null;

        foreach ($this->paymentDetails->getOperations() as $paymentDetail) {
            $payment = $this->safeGetPayment($storeId, $paymentDetail->getId());
            if (!$payment) {
                continue;
            }

            $productId = $this->getPaymentProductId($payment);
            if ($this->isSplitPaymentProduct($productId)
                && $paymentDetail->getStatus() === 'CAPTURED'
            ) {
                $operationAmount = $productId === PaymentProductsDetailsInterface::ILLICADO_PRODUCT_ID
                    ? (int) $paymentDetail->getAmountOfMoney()->getAmount()
                    : $this->calculateNonIllicadoSplitAmount($payment);
                $this->splitPayments[] = [
                    'payment' => $payment,
                    'operationAmount' => $operationAmount,
                ];
            } elseif (!$this->mainPaymentResponse) {
                $this->mainPaymentResponse = $payment;
            }
        }

        return $this->splitPayments;
    }

    /**
     * Calculate split amount for non-Illicado products (Cheque Vacances, Mealvouchers).
     */
    private function calculateNonIllicadoSplitAmount($payment): int
    {
        $operations = $this->paymentDetails->getOperations();
        $lastOperation = end($operations);
        return (int) ($payment->getPaymentOutput()->getAmountOfMoney()->getAmount() -
            $lastOperation->getAmountOfMoney()->getAmount());
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

    private function applyMainPaymentDetails(PaymentInfoInterface $paymentInformation): void
    {
        if (!$this->mainPaymentResponse) {
            return;
        }

        $output = $this->getOutput($this->mainPaymentResponse);

        $cardOutput = $output->getCardPaymentMethodSpecificOutput();
        if ($cardOutput) {
            $paymentInformation->setPaymentProductId($cardOutput->getPaymentProductId());
            $paymentInformation->setPaymentMethod(
                PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$cardOutput->getPaymentProductId()]['group'] ?? ''
            );
            if ($cardOutput->getCard()) {
                $paymentInformation->setCardLastNumbers(trim($cardOutput->getCard()->getCardNumber(), '*'));
            }

            return;
        }

        $redirectOutput = $output->getRedirectPaymentMethodSpecificOutput();
        if ($redirectOutput) {
            $paymentInformation->setPaymentProductId($redirectOutput->getPaymentProductId());
            $paymentInformation->setPaymentMethod(
                PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$redirectOutput->getPaymentProductId()]['group'] ?? ''
            );
        }
    }

    private function analyzeOperationsByMethod(): array
    {
        if ($this->operationAnalysis !== null) {
            return $this->operationAnalysis;
        }

        $this->operationAnalysis = [];

        if (!$this->paymentDetails || !$this->paymentDetails->getOperations()) {
            return $this->operationAnalysis;
        }

        foreach ($this->paymentDetails->getOperations() as $operation) {
            $method = $operation->getPaymentMethod();
            $key = self::STATUS_AMOUNT_MAP[(int) $operation->getStatusOutput()->getStatusCode()] ?? null;
            if (!$method || !$key) {
                continue;
            }

            if (!isset($this->operationAnalysis[$method])) {
                $this->operationAnalysis[$method] = ['captured' => 0, 'refunded' => 0, 'pending_refund' => 0];
            }

            $this->operationAnalysis[$method][$key] += (int) $operation->getAmountOfMoney()->getAmount();
        }

        return $this->operationAnalysis;
    }

    private function applySplitRefundData(PaymentInfoInterface $paymentInfo, int $capturedAmountCents): void
    {
        $analysis = $this->analyzeOperationsByMethod();
        $redirectData = $analysis['redirect'] ?? null;
        if (!$redirectData || $redirectData['captured'] <= 0) {
            return;
        }

        $currency = $paymentInfo->getCurrency();
        $ratio = $capturedAmountCents / $redirectData['captured'];

        $refunded = (int) round($redirectData['refunded'] * $ratio);
        $pendingRefund = (int) round($redirectData['pending_refund'] * $ratio);

        if ($refunded >= $capturedAmountCents) {
            $paymentInfo->setStatus('REFUNDED');
            $paymentInfo->setStatusCode(self::STATUS_CODE_REFUNDED);
        } elseif ($pendingRefund > 0) {
            $paymentInfo->setStatus('PENDING_REFUND');
            $paymentInfo->setStatusCode(self::STATUS_CODE_PENDING_REFUND);
        }

        if ($refunded > 0) {
            $paymentInfo->setRefundedAmount(
                $this->paymentInfoBuilder->getFormattedSplitPaymentAmount($refunded, $currency)
            );
        }

        $available = $capturedAmountCents - $refunded - $pendingRefund;
        if ($available > 0) {
            $paymentInfo->setAmountAvailableForRefund(
                $this->paymentInfoBuilder->getFormattedSplitPaymentAmount($available, $currency)
            );
        }
    }

    private function applyMainPaymentRefundData(PaymentInfoInterface $paymentInformation): void
    {
        $analysis = $this->analyzeOperationsByMethod();
        $cardData = $analysis['card'] ?? null;
        if (!$cardData) {
            $formattedSplitPaymentAmount = $this->paymentInfoBuilder->
            getFormattedSplitPaymentAmount($this->totalSplitPaymentAmountCents, $paymentInformation->getCurrency());
            $remainingAmount = $paymentInformation->getAuthorizedAmount() - $formattedSplitPaymentAmount;
            if ($remainingAmount > 0) {
                $paymentInformation->setAuthorizedAmount($remainingAmount);
            }
            return;
        }

        $currency = $paymentInformation->getCurrency();

        $paymentInformation->setAuthorizedAmount(
            $this->paymentInfoBuilder->getFormattedSplitPaymentAmount($cardData['captured'], $currency)
        );

        if ($cardData['refunded'] >= $cardData['captured'] && $cardData['captured'] > 0) {
            $paymentInformation->setStatus('REFUNDED');
            $paymentInformation->setStatusCode(8);
        } elseif ($cardData['pending_refund'] > 0) {
            $paymentInformation->setStatus('PENDING_REFUND');
            $paymentInformation->setStatusCode(81);
        }

        if ($cardData['refunded'] > 0) {
            $paymentInformation->setRefundedAmount(
                $this->paymentInfoBuilder->getFormattedSplitPaymentAmount($cardData['refunded'], $currency)
            );
        } else {
            $paymentInformation->setRefundedAmount(0);
        }

        $available = $cardData['captured'] - $cardData['refunded'] - $cardData['pending_refund'];
        if ($available > 0) {
            $paymentInformation->setAmountAvailableForRefund(
                $this->paymentInfoBuilder->getFormattedSplitPaymentAmount($available, $currency)
            );
        } else {
            $paymentInformation->setAmountAvailableForRefund(0);
        }
    }

    /**
     * Check if a product ID is one of the split payment product types.
     */
    private function isSplitPaymentProduct(?int $productId): bool
    {
        return in_array($productId, [
            PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID,
            PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID,
            PaymentProductsDetailsInterface::ILLICADO_PRODUCT_ID,
        ], true);
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
