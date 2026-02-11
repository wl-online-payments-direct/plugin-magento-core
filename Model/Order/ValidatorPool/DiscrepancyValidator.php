<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\ValidatorPool;

use Worldline\PaymentCore\Api\Data\PaymentInterface as WlPaymentInterface;
use Worldline\PaymentCore\Api\Data\TransactionInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;
use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Api\TransactionRepositoryInterface;

class DiscrepancyValidator
{
    /**
     * @var CurrencyAmountNormalizer
     */
    private $normalizer;
    /**
     * @var PaymentRepositoryInterface
     */
    private $wlPaymentRepository;
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        CurrencyAmountNormalizer $normalizer,
        PaymentRepositoryInterface $wlPaymentRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->normalizer = $normalizer;
        $this->wlPaymentRepository = $wlPaymentRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param float $orderTotal
     * @param string $incrementId
     *
     * @return bool
     */
    public function compareAmounts(float $orderTotal, string $incrementId): bool
    {
        $wlPayment = $this->getWlPayment($incrementId);
        if (!$wlPayment->getAmount() || !$wlPayment->getCurrency()) {
            return false;
        }
        $paidAmount = $this->normalizer->normalize((float)$wlPayment->getAmount(), $wlPayment->getCurrency());

        return $orderTotal !== $paidAmount;
    }

    /**
     * @param string $incrementId
     *
     * @return WlPaymentInterface|TransactionInterface|null
     */
    public function getWlPayment(string $incrementId)
    {
        $wlPayment = $this->wlPaymentRepository->get($incrementId);

        return $wlPayment->getPaymentId() ? $wlPayment : $this->transactionRepository->getLastTransaction($incrementId);
    }
}
