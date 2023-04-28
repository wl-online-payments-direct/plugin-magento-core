<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order;

use Magento\Framework\DataObject;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterface;

/**
 * Context for the validator pool
 *
 * @method getStatusCode(): ?int
 * @method setStatusCode(int $statusCode): CanPlaceOrderContextInterface
 * @method getWorldlinePaymentId(): ?string
 * @method setWorldlinePaymentId(string $worldlinePaymentId): CanPlaceOrderContextInterface
 * @method getIncrementId(): ?string
 * @method setIncrementId(?string $incrementId): CanPlaceOrderContextInterface
 * @method getStoreId(): ?int
 * @method setStoreId(int $storeId): CanPlaceOrderContextInterface
 */
class CanPlaceContext extends DataObject implements CanPlaceOrderContextInterface
{

}
