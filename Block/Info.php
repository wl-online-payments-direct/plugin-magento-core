<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use Worldline\PaymentCore\Api\Data\PaymentInfoInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\InfoFormatterInterface;
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
     * @var string
     */
    protected $_template = 'Worldline_PaymentCore::info/default.phtml';

    public function __construct(
        Context $context,
        PaymentIconsProviderInterface $paymentIconProvider,
        PaymentInfoBuilder $paymentInfoBuilder,
        InfoFormatterInterface $infoFormatter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentIconProvider = $paymentIconProvider;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
        $this->infoFormatter = $infoFormatter;
    }

    public function getSpecificInformation(): array
    {
        return $this->infoFormatter->format($this->getPaymentInformation());
    }

    public function getPaymentTitle(): string
    {
        $paymentProductId = $this->getPaymentInformation()->getPaymentProductId();
        $methodUsed = ($paymentProductId)
            ? PaymentProductsDetailsInterface::PAYMENT_PRODUCTS[$paymentProductId]['label']
            : __('Payment');

        return __('%1 with Worldline', $methodUsed)->render();
    }

    public function getIconUrl(): string
    {
        return $this->getIconForType()['url'] ?? '';
    }

    public function getIconWidth(): int
    {
        return $this->getIconForType()['width'];
    }

    public function getIconHeight(): int
    {
        return $this->getIconForType()['height'];
    }

    public function getIconTitle(): Phrase
    {
        return __($this->getIconForType()['title']);
    }

    public function getAspectRatio(): string
    {
        return $this->getIconWidth() . '/' . $this->getIconHeight();
    }

    public function getMaxHeight(): string
    {
        return self::MAX_HEIGHT;
    }

    private function getIconForType(): array
    {
        $storeId = (int)$this->getInfo()->getOrder()->getStoreId();
        return $this->paymentIconProvider->getIconById($this->getPaymentInformation()->getPaymentProductId(), $storeId);
    }

    public function getPaymentInformation(): PaymentInfoInterface
    {
        if (null === $this->paymentInformation) {
            $this->paymentInformation = $this->paymentInfoBuilder->build($this->getInfo()->getOrder());
        }

        return $this->paymentInformation;
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
}
