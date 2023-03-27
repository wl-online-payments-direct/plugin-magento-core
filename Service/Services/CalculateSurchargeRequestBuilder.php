<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Services;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequestFactory;
use OnlinePayments\Sdk\Domain\CardSource;
use OnlinePayments\Sdk\Domain\CardSourceFactory;
use Worldline\PaymentCore\Api\Service\CalculateSurchargeRequestBuilderInterface;
use Worldline\PaymentCore\Service\CreateRequest\Order\AmountDataBuilder;

/**
 * @link https://support.direct.worldline-solutions.com/en/documentation/api/reference/#tag/Services/operation/SurchargeCalculation
 */
class CalculateSurchargeRequestBuilder implements CalculateSurchargeRequestBuilderInterface
{
    /**
     * @var CardSourceFactory
     */
    private $cardSourceFactory;

    /**
     * @var AmountDataBuilder
     */
    private $amountDataBuilder;

    /**
     * @var CalculateSurchargeRequestFactory
     */
    private $calculateSurchargeRequestFactory;

    public function __construct(
        CardSourceFactory $cardSourceFactory,
        AmountDataBuilder $amountDataBuilder,
        CalculateSurchargeRequestFactory $calculateSurchargeRequestFactory
    ) {
        $this->cardSourceFactory = $cardSourceFactory;
        $this->amountDataBuilder = $amountDataBuilder;
        $this->calculateSurchargeRequestFactory = $calculateSurchargeRequestFactory;
    }

    public function build(CartInterface $quote, string $hostedTokenizationId): CalculateSurchargeRequest
    {
        /** @var CardSource $cardSource */
        $cardSource = $this->cardSourceFactory->create();
        $cardSource->setHostedTokenizationId($hostedTokenizationId);

        /** @var CalculateSurchargeRequest $calculateSurchargeRequest */
        $calculateSurchargeRequest = $this->calculateSurchargeRequestFactory->create();
        $calculateSurchargeRequest->setCardSource($cardSource);
        $calculateSurchargeRequest->setAmountOfMoney($this->amountDataBuilder->build($quote));

        return $calculateSurchargeRequest;
    }
}
