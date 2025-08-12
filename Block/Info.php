<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\InfoFormatterInterface;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\Model\Transaction\PaymentInfoBuilder;
use Worldline\PaymentCore\Api\Ui\PaymentIconsProviderInterface;

class Info extends Template
{
    public const MAX_HEIGHT = '40px';

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
     * @var string
     */
    protected $_template = 'Worldline_PaymentCore::info/default.phtml';

    public function __construct(
        Context $context,
        PaymentIconsProviderInterface $paymentIconProvider,
        PaymentInfoBuilder $paymentInfoBuilder,
        InfoFormatterInterface $infoFormatter,
        ClientProviderInterface $clientProvider,
        WorldlineConfig $worldlineConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentIconProvider = $paymentIconProvider;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
        $this->infoFormatter = $infoFormatter;
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
    }

    public function getSpecificInformation(): array
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
            $paymentInformation->setAuthorizedAmount($paymentInformation->getAuthorizedAmount() - $formattedSplitPaymentAmount);
        }
        $specificInformation[] = array_merge(
            $specificInformation,
            $this->infoFormatter->format($this->getPaymentInformation())
        );

        return $specificInformation;
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

        if (null === $this->paymentDetails) {
            $this->paymentDetails = $this->clientProvider->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->getPaymentDetails($this->paymentInfoBuilder->getPaymentByOrderId(
                    $this->getInfo()->getOrder()));
        }
        $this->splitPayment = ['payment' => null];

        foreach ($this->paymentDetails->getOperations() as $paymentDetail) {
            try {
                $payment = $this->clientProvider->getClient($storeId)
                    ->merchant($this->worldlineConfig->getMerchantId($storeId))
                    ->payments()
                    ->getPayment($paymentDetail->getId());

                $paymentOutput = $this->getOutput($payment);
                $redirectPaymentMethodSpecificOutput = $paymentOutput ?
                    $paymentOutput->getRedirectPaymentMethodSpecificOutput() : null;
                $paymentProductId = $redirectPaymentMethodSpecificOutput ?
                    $redirectPaymentMethodSpecificOutput->getPaymentProductId() : null;

                if (
                    $paymentProductId === PaymentProductsDetailsInterface::CHEQUE_VACANCES_CONNECT_PRODUCT_ID
                    || $paymentProductId === PaymentProductsDetailsInterface::MEALVOUCHERS_PRODUCT_ID
                ) {
                    $this->splitPayment['payment'] = $payment;
                }
            } catch (\Exception $e) {
            }
        }
        $payment = $this->splitPayment['payment'];
        if (!$payment) {
            return null;
        }
        $this->setSplitPaymentFinalStatus($payment);
        $this->splitPaymentAmount = $payment->getPaymentOutput()->getAmountOfMoney()->getAmount() - $paymentDetail->getAmountOfMoney()->getAmount();

        return $this->paymentInfoBuilder->buildSplitTransaction($payment, (int) $this->splitPaymentAmount);
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
