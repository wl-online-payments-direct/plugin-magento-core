<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Worldline\PaymentCore\Model\Order\Creation\DateLimitProvider;
use Worldline\PaymentCore\Model\Order\Creation\OrderCollectionFactory;

class PendingOrderProvider
{
    /**
     * @var string[]
     */
    private $allowedPaymentMethods;

    /**
     * @var OrderCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var DateLimitProvider
     */
    private $dateLimitProvider;

    public function __construct(
        QuoteCollectionFactory $quoteCollectionFactory,
        DateLimitProvider $dateLimitProvider,
        array $allowedPaymentMethods = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->dateLimitProvider = $dateLimitProvider;
        $this->allowedPaymentMethods = $allowedPaymentMethods;
    }

    public function getReservedOrderIds(): array
    {
        $collection = $this->quoteCollectionFactory->create();

        $this->addTimeToFilter($collection);
        $this->addAllowedPaymentMethods($collection);
        $this->addWithoutOrderFilter($collection);

        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['main_table.reserved_order_id']);

        return $collection->getConnection()->fetchCol($collection->getSelect());
    }

    private function addTimeToFilter(QuoteCollection $collection): void
    {
        if ($dateTo = $this->dateLimitProvider->getDateTo()) {
            $collection->addFieldToFilter('main_table.created_at', ['lteq' => $dateTo]);
        }

        if ($dateFrom = $this->dateLimitProvider->getDateFrom()) {
            $collection->addFieldToFilter('main_table.created_at', ['gteq' => $dateFrom]);
        }
    }

    private function addAllowedPaymentMethods(QuoteCollection $collection): void
    {
        $collection->getSelect()
            ->joinInner(
                ['qp' => $collection->getTable('quote_payment')],
                'main_table.entity_id = qp.quote_id',
                ['method']
            )
            ->joinLeft(
                ['wfpl' => $collection->getTable(FailedPaymentLog::TABLE)],
                'qp.payment_id = wfpl.quote_payment_id',
                ['method']
            )
            ->where('wfpl.quote_payment_id IS NULL')
            ->where('qp.method IN (?)', $this->allowedPaymentMethods);
    }

    private function addWithoutOrderFilter(QuoteCollection $collection): void
    {
        $collection->getSelect()
            ->joinLeft(
                ['so' => $collection->getTable('sales_order')],
                'main_table.entity_id = so.quote_id'
            )
            ->where('so.increment_id IS NULL');
    }
}
