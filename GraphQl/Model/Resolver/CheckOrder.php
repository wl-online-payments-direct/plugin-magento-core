<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\GraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\OrderFactory;

class CheckOrder implements ResolverInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): bool
    {
        if (empty($args['incrementId'])) {
            return false;
        }

        $order = $this->orderFactory->create()->loadByIncrementId($args['incrementId']);

        return (bool)$order->getId();
    }
}
