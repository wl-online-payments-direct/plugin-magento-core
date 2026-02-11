<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Gateway\Command;

use Worldline\PaymentCore\Model\Order\CurrencyAmountNormalizer;
use Worldline\PaymentCore\Model\Order\ValidatorPool\DiscrepancyValidator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Gateway\SubjectReader;

/**
 * Used for Magento 2.3.7
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * Worldline authorize and capture command
     */
    public const SALE = 'sale';

    /**
     * Capture command
     */
    public const CAPTURE = 'settlement';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    /**
     * @var DiscrepancyValidator
     */
    private $discrepancyValidator;

    /**
     * @var CurrencyAmountNormalizer
     */
    private $currencyNormalizer;

    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        DiscrepancyValidator $discrepancyValidator,
        CurrencyAmountNormalizer $currencyNormalizer
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->discrepancyValidator = $discrepancyValidator;
        $this->currencyNormalizer = $currencyNormalizer;
    }

    /**
     * Identify payment action
     *
     * @param array $commandSubject
     * @return void
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute(array $commandSubject): void
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        if ($this->isOrderWithDiscrepancy($paymentDO->getOrder())) {
            $wlPayment = $this->discrepancyValidator->getWlPayment($paymentDO->getOrder()->getOrderIncrementId());
            $commandSubject['amount'] = $this->currencyNormalizer->normalize(
                (float)$wlPayment->getAmount(),
                $wlPayment->getCurrency()
            );
        }

        if ($orderId = (int)$paymentDO->getOrder()->getId()) {
            $surchargingQuote = $this->surchargingQuoteRepository->getByOrderId($orderId);
            if ($surchargingQuote->getId()) {
                $commandSubject['amount'] -= (float)$surchargingQuote->getAmount();
            }
        }

        $command = $this->getCommand($paymentDO);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    private function getCommand(PaymentDataObjectInterface $paymentDO): string
    {
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        // if auth transaction does not exist then execute authorize&capture command
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$existsCapture && !$payment->getAuthorizationTransaction()) {
            return self::SALE;
        }

        return self::CAPTURE;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment): bool
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (bool)$count;
    }

    /**
     * @param OrderAdapterInterface $order
     *
     * @return bool
     */
    private function isOrderWithDiscrepancy(OrderAdapterInterface $order): bool
    {
        return $this->discrepancyValidator->compareAmounts(
            (float)$order->getGrandTotalAmount(),
            $order->getOrderIncrementId()
        );
    }
}
