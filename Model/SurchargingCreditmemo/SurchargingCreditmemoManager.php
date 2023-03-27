<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\SurchargingCreditmemo;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Worldline\PaymentCore\Api\Data\SurchargingCreditmemoInterfaceFactory;
use Worldline\PaymentCore\Api\SurchargingCreditmemoManagerInterface;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;

class SurchargingCreditmemoManager implements SurchargingCreditmemoManagerInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var SurchargingCreditmemoInterfaceFactory
     */
    private $surchargingCreditmemoFactory;

    /**
     * @var SurchargingCreditmemoRepositoryInterface
     */
    private $surchargingCreditmemoRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        SurchargingCreditmemoInterfaceFactory $surchargingCreditmemoFactory,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->surchargingCreditmemoFactory = $surchargingCreditmemoFactory;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function createSurcharging(int $creditmemoId, int $quoteId, float $surchargingAmount): void
    {
        $surchargingCreditmemo = $this->surchargingCreditmemoFactory->create();
        $surchargingCreditmemo->setQuoteId($quoteId);
        $surchargingCreditmemo->setCreditmemoId($creditmemoId);
        $surchargingCreditmemo->setAmount($surchargingAmount);
        $surchargingBaseAmount = $this->priceCurrency->convertAndRound($surchargingAmount);
        $surchargingCreditmemo->setBaseAmount($surchargingBaseAmount);

        $this->surchargingCreditmemoRepository->save($surchargingCreditmemo);
    }
}
