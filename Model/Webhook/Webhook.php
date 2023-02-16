<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Webhook;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Api\Data\WebhookInterface;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook as WebhookResource;

/**
 * Worldline webhook entity
 *
 * @method getIncrementId(): string
 * @method setIncrementId(string $incrementId): WebhookInterface
 *
 * @method getType(): ?string
 * @method setType(string $type): WebhookInterface
 *
 * @method getStatusCode(): ?int
 * @method setStatusCode(string $statusCode): WebhookInterface
 *
 * @method getBody(): ?string
 * @method setBody(string $body): WebhookInterface
 */
class Webhook extends AbstractModel implements WebhookInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'worldline_webhook';

    protected function _construct(): void
    {
        $this->_init(WebhookResource::class);
    }
}
