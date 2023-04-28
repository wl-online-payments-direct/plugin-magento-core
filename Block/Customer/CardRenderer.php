<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

class CardRenderer extends AbstractCardRenderer
{
    /**
     * @var array
     */
    private $paymentMethods;

    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        array $data = [],
        array $paymentMethods = []
    ) {
        parent::__construct($context, $iconsProvider, $data);
        $this->paymentMethods = $paymentMethods;
    }

    public function canRender(PaymentTokenInterface $token): bool
    {
        if (in_array($token->getPaymentMethodCode(), $this->paymentMethods, true)) {
            return true;
        }
        return false;
    }

    public function getNumberLast4Digits(): string
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    public function getExpDate(): string
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    public function getIconUrl(): string
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    public function getIconHeight(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    public function getIconWidth(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }
}
