<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\DataObject;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Payment\CancelPaymentServiceInterface;
use Worldline\PaymentCore\Gateway\Request\VoidAndCancelDataBuilder;

class TransactionVoid extends AbstractTransaction
{
    /**
     * @var CancelPaymentServiceInterface
     */
    private $cancelPaymentService;

    public function __construct(
        LoggerInterface $logger,
        CancelPaymentServiceInterface $cancelPaymentService
    ) {
        parent::__construct($logger);
        $this->cancelPaymentService = $cancelPaymentService;
    }

    /**
     * @param array $data
     * @return DataObject
     * @throws LocalizedException
     */
    protected function process(array $data): DataObject
    {
        return $this->cancelPaymentService->execute(
            $data[VoidAndCancelDataBuilder::TRANSACTION_ID],
            $data[VoidAndCancelDataBuilder::STORE_ID]
        );
    }
}
