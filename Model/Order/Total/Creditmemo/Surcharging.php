<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Order\Total\Creditmemo;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Worldline\PaymentCore\Api\Data\SurchargingCreditmemoInterface;
use Worldline\PaymentCore\Api\Data\SurchargingQuoteInterface;
use Worldline\PaymentCore\Api\SurchargingCreditmemoRepositoryInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteRepositoryInterface;
use Worldline\PaymentCore\Model\Quote\Surcharging as QuoteSurcharging;

class Surcharging extends AbstractTotal
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

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
        PriceCurrencyInterface $priceCurrency,
        SurchargingQuoteRepositoryInterface $surchargingQuoteRepository,
        SurchargingCreditmemoRepositoryInterface $surchargingCreditmemoRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->request = $request;
        $this->priceCurrency = $priceCurrency;
        $this->surchargingQuoteRepository = $surchargingQuoteRepository;
        $this->surchargingCreditmemoRepository = $surchargingCreditmemoRepository;
    }

    public function collect(Creditmemo $creditmemo): Surcharging
    {
        $order = $creditmemo->getOrder();
        if (!$order->getPayment()) {
            return $this;
        }

        $quoteId = (int)$order->getQuoteId();
        $surchargingQuote = $this->surchargingQuoteRepository->getByQuoteId($quoteId);
        $paymentMethod = str_replace('_vault', '', (string)$order->getPayment()->getMethod());
        if (!$surchargingQuote->getId()
            || $paymentMethod !== $surchargingQuote->getPaymentMethod()
            || $surchargingQuote->getIsRefunded()
        ) {
            return $this;
        }

        [$surchargeAmount, $surchargeBaseAmount] = $this->calculateAndValidateSurchargeAmounts(
            $quoteId,
            $surchargingQuote
        );
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $surchargeAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $surchargeBaseAmount);

        return $this;
    }

    /**
     * @param int $quoteId
     * @param SurchargingQuoteInterface $surchargingQuote
     * @return array
     * @throws LocalizedException
     */
    private function calculateAndValidateSurchargeAmounts(
        int $quoteId,
        SurchargingQuoteInterface $surchargingQuote
    ): array {
        $refundedSurchargeAmount = 0;
        $refundedSurchargeBaseAmount = 0;
        /** @var SurchargingCreditmemoInterface $surchargingCreditmemo */
        foreach ($this->surchargingCreditmemoRepository->getItemsByQuoteId($quoteId) as $refundedSurchargeItem) {
            $refundedSurchargeAmount += $refundedSurchargeItem->getAmount();
            $refundedSurchargeBaseAmount += $refundedSurchargeItem->getBaseAmount();
        }

        $remainingSurchargeAmount = $surchargingQuote->getAmount() - $refundedSurchargeAmount;
        $remainingSurchargeBaseAmount = $surchargingQuote->getBaseAmount() - $refundedSurchargeBaseAmount;

        $enteredSurchargeAmount = $this->request->getParam('creditmemo')[QuoteSurcharging::CODE] ?? null;
        if (null === $enteredSurchargeAmount) {
            return [$remainingSurchargeAmount, $remainingSurchargeBaseAmount];
        }

        if ($enteredSurchargeAmount < 0) {
            throw new LocalizedException(__('Surcharging amount must not be less than zero.'));
        }

        if (($enteredSurchargeAmount - $remainingSurchargeAmount) > 0.00001) {
            throw new LocalizedException(__('Surcharging amount must not be more than remaining value.'));
        }

        $enteredSurchargeBaseAmount = $this->priceCurrency->convertAndRound($enteredSurchargeAmount);
        return [$enteredSurchargeAmount, $enteredSurchargeBaseAmount];
    }
}
