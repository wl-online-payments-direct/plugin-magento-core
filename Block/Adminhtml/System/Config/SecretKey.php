<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Encryption\EncryptorInterface;

class SecretKey extends Field
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->encryptor = $encryptor;
    }

    public function render(AbstractElement $element): string
    {
        $value = (string) $element->getEscapedValue();
        if ($value) {
            $value = substr($this->encryptor->decrypt($value), 0, 5) . '******';
            $element->setValue($value);
        }

        return parent::render($element);
    }
}
