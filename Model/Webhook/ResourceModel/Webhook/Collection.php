<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook as WebhookResource;
use Worldline\PaymentCore\Model\Webhook\Webhook as WebhookModel;

/**
 * Collection for webhook entity
 */
class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(WebhookModel::class, WebhookResource::class);
    }
}
