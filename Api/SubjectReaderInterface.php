<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnlinePayments\Sdk\Domain\DataObject;

interface SubjectReaderInterface
{
    public function readResponseObject(array $subject): object;

    public function readPayment(array $subject): PaymentDataObjectInterface;

    public function readTransaction(array $subject): DataObject;

    public function readAmount(array $subject);
}
