<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\RefundRequest;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Worldline\PaymentCore\Api\Data\RefundRequestInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;

class RefundProcessor
{
    /**
     * @var EmailNotification
     */
    private $emailNotification;

    /**
     * @var CreditmemoOfflineService
     */
    private $refundOfflineService;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    public function __construct(
        EmailNotification $emailNotification,
        CreditmemoOfflineService $refundOfflineService,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundRequestRepositoryInterface $refundRequestRepository
    ) {
        $this->emailNotification = $emailNotification;
        $this->refundOfflineService = $refundOfflineService;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundRequestRepository = $refundRequestRepository;
    }

    public function process(RefundRequestInterface $refundRequest): void
    {
        $creditmemoEntity = $this->creditmemoRepository->get($refundRequest->getCreditMemoId());

        $this->refundOfflineService->refund($creditmemoEntity);

        $refundRequest->setRefunded(true);
        $this->refundRequestRepository->save($refundRequest);

        $this->emailNotification->send($creditmemoEntity);
    }

    /**
     * @param $creditMemoId
     *
     * @return CreditmemoInterface
     */
    public function getCreditMemoById($creditMemoId)
    {
        return $this->creditmemoRepository->get($creditMemoId);
    }
}
