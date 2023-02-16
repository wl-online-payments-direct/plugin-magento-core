<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Customer;

use Magento\Framework\View\Element\Template;
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
        \Magento\Payment\Model\CcConfigProvider $iconsProvider,
        array $data = [],
        array $paymentMethods = []
    ) {
        parent::__construct($context, $iconsProvider, $data);
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return bool
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        if (in_array($token->getPaymentMethodCode(), $this->paymentMethods)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits(): string
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    /**
     * @return string
     */
    public function getExpDate(): string
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    /**
     * @return string
     */
    public function getIconUrl(): string
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * @return int
     */
    public function getIconHeight(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * @return int
     */
    public function getIconWidth(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }
}
