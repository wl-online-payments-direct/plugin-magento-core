<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\PaymentCore\Api\Data\RefundRequestInterface;
use Worldline\PaymentCore\Api\RefundRequestRepositoryInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Api\Webhook\ProcessorInterface;

class RejectPaymentProcessor implements ProcessorInterface
{
    public const REFUND_REFUSED_CODE = 83;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        OrderRepositoryInterface $orderRepository,
        OrderConfig $orderConfig
    ) {
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->orderRepository = $orderRepository;
        $this->orderConfig = $orderConfig;
    }

    /**
     * Handled refused refund webhook only
     *
     * @param WebhooksEvent $webhookEvent
     * @return void
     */
    public function process(WebhooksEvent $webhookEvent): void
    {
        /** @var RefundResponse $refundResponse */
        $refundResponse = $webhookEvent->getRefund();
        if (!$refundResponse) {
            return;
        }

        $statusCode = (int)$refundResponse->getStatusOutput()->getStatusCode();
        if ($statusCode === self::REFUND_REFUSED_CODE) {
            $incrementId = $refundResponse->getRefundOutput()->getReferences()->getMerchantReference();
            $amount = (int)$refundResponse->getRefundOutput()->getAmountOfMoney()->getAmount();
            $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount((string)$incrementId, $amount);
            if (!$refundRequest->getCreditMemoId()) {
                return;
            }

            $this->transactionWLResponseManager->saveTransaction($refundResponse);

            $this->processRefused($refundRequest);
        }
    }

    private function processRefused(RefundRequestInterface $refundRequest): void
    {
        $creditmemoEntity = $this->creditmemoRepository->get($refundRequest->getCreditMemoId());
        $order = $creditmemoEntity->getOrder();

        $creditmemoEntity->setState(Creditmemo::STATE_CANCELED);
        $order->setState(Order::STATE_CANCELED);
        $order->setStatus($this->orderConfig->getStateDefaultStatus(Order::STATE_CANCELED));

        $this->creditmemoRepository->save($creditmemoEntity);
        $this->orderRepository->save($order);
    }
}
