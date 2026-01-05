<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Api\Service\CreateRequest;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Feedbacks;

interface FeedbacksDataBuilderInterface
{
    public function build(CartInterface $quote): ?Feedbacks;
}
