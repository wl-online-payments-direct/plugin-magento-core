<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\RefundResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Refund\CreateRefundServiceInterface;
use Worldline\PaymentCore\Gateway\Request\RefundDataBuilder;

class TransactionRefund extends AbstractTransaction
{
    /**
     * @var CreateRefundServiceInterface
     */
    private $createRefundService;

    public function __construct(
        LoggerInterface $logger,
        CreateRefundServiceInterface $createRefundService
    ) {
        parent::__construct($logger);
        $this->createRefundService = $createRefundService;
    }

    /**
     * Create refund transaction
     *
     * @param array $data
     * @return DataObject|RefundResponse
     * @throws LocalizedException
     */
    protected function process(array $data): DataObject
    {
        return $this->createRefundService->execute(
            $data[RefundDataBuilder::TRANSACTION_ID],
            $data[RefundDataBuilder::REFUND_REQUEST],
            $data[RefundDataBuilder::STORE_ID]
        );
    }
}
