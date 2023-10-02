<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Worldline\PaymentCore\Api\Data\RefundRequestInterface;

class RefundRefusedProcessor
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    public function __construct(
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
    }

    public function process(RefundRequestInterface $refundRequest): void
    {
        $creditmemoEntity = $this->creditmemoRepository->get($refundRequest->getCreditMemoId());

        $creditmemoEntity->setState(Creditmemo::STATE_CANCELED);

        $this->creditmemoRepository->save($creditmemoEntity);
    }
}
