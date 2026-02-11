<?php
namespace Worldline\PaymentCore\Model\OrderState;

use Magento\Framework\App\ResourceConnection;

class OrderStateHelper
{
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param string $status
     * @return string|null  state name or null if not found
     */
    public function getStateByStatus(string $status): ?string
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('sales_order_status_state'); // accounts for prefixes

        $select = $connection->select()
            ->from($table, ['state'])
            ->where('status = ?', $status)
            ->limit(1);

        $result = $connection->fetchOne($select);

        return $result !== false ? (string)$result : null;
    }
}
