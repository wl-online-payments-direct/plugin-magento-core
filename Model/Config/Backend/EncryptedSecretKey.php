<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Encrypted;

/**
 * Encrypted config field backend model
 */
class EncryptedSecretKey extends Encrypted
{
    /**
     * Encrypt value before saving
     *
     * @return void
     */
    public function beforeSave(): void
    {
        $this->_dataSaveAllowed = false;
        $value = (string)$this->getValue();
        // Check if an obscured value was received. It should contain 6 * symbols.
        if (!empty($value) && !preg_match('/\*\*\*\*\*\*/', $value)) {
            $this->_dataSaveAllowed = true;
            $encrypted = $this->_encryptor->encrypt($value);
            $this->setValue($encrypted);
        } elseif (empty($value)) {
            $this->_dataSaveAllowed = true;
        }
    }
}
