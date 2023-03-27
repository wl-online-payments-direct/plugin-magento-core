<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Observer\Sales\Model\Order\Creditmemo;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Worldline\PaymentCore\Api\SurchargingCreditmemoManagerInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;
use Worldline\PaymentCore\Model\Quote\Surcharging as QuoteSurcharging;

/**
 * Save surcharging data when submit credit memo, 'sales_order_creditmemo_save_after' event
 */
class IsRefundedSurcharging implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SurchargingCreditmemoManagerInterface
     */
    private $surchargingCreditmemoManager;

    /**
     * @var SurchargingQuoteRepositoryInterface
     */
    private $surchargingQuoteRepository;

    /**
     * @var SurchargingCreditmemoRepositoryInterface
     */
    private $surchargingCreditmemoRepository;

    public function __construct(
        RequestInterface $request,
        SurchargingCreditmemoManagerInterface $surchargingCreditmemoManager,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository
    ) {
        $this->request = $request;
        $this->surchargingCreditmemoManager = $surchargingCreditmemoManager;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function execute(Observer $observer): void
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $quoteId = (int)$creditmemo->getOrder()->getQuoteId();
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        if (!$surchargingQuote->getId() || $surchargingQuote->getIsRefunded()) {
            return;
        }

        $enteredCreditmemoData = $this->request->getParam('creditmemo');
        if (!$enteredCreditmemoData || empty($enteredCreditmemoData[QuoteSurcharging::CODE])) {
            return;
        }

        $enteredAmount = (float)$enteredCreditmemoData[QuoteSurcharging::CODE];
        $this->surchargingCreditmemoManager->createSurcharging((int)$creditmemo->getId(), $quoteId, $enteredAmount);

        if ($this->getRemainingAmount($quoteId) == $surchargingQuote->getAmount()) {
            $surchargingQuote->setIsRefunded(true);
            $this->surchargingQuoteRepository->save($surchargingQuote);
        }
    }

    private function getRemainingAmount(int $quoteId): float
    {
        $remainingSurchargeAmount = 0.0;
        foreach ($this->surchargingCreditmemoRepository->getItemsByQuoteId($quoteId) as $surchargingCreditmemo) {
            $remainingSurchargeAmount += $surchargingCreditmemo->getAmount();
        }

        return $remainingSurchargeAmount;
    }
}
