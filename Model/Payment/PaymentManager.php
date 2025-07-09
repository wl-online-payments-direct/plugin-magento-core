<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use OnlinePayments\Sdk\Domain\DataObject;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\PaymentRepositoryInterface;

/**
 * Manager for worldline payment entity
 */
class PaymentManager implements PaymentManagerInterface
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->paymentRepository = $paymentRepository;
    }

    public function savePayment(DataObject $worldlineResponse): PaymentInterface
    {
        $incrementId = $worldlineResponse->getPaymentOutput()->getReferences()->getMerchantReference();
        $wlPayment = $this->paymentRepository->get($incrementId);
        if ($wlPayment->getId()) {
            return $wlPayment;
        }

        $this->addCardPaymentMethodData($worldlineResponse, $wlPayment);
        $this->addRedirectPaymentMethodData($worldlineResponse, $wlPayment);
        $this->addSepaPaymentMethodData($worldlineResponse, $wlPayment);

        return $this->paymentRepository->save($wlPayment);
    }

    public function updatePayment(DataObject $worldlineResponse): PaymentInterface
    {
        $this->paymentRepository->deleteByIncrementId(
            (string) $worldlineResponse->getPaymentOutput()->getReferences()->getMerchantReference()
        );

        return $this->savePayment($worldlineResponse);
    }

    private function addCardPaymentMethodData(DataObject $worldlineResponse, PaymentInterface $wlPayment): void
    {
        $output = $worldlineResponse->getPaymentOutput();
        $cardPaymentMethod = $output->getCardPaymentMethodSpecificOutput();
        if (!$cardPaymentMethod || !$cardPaymentMethod->getCard()) {
            return;
        }

        $wlPayment->setCardNumber(trim($cardPaymentMethod->getCard()->getCardNumber(), '*'));
        $wlPayment->setIncrementId($output->getReferences()->getMerchantReference());
        $wlPayment->setPaymentId($worldlineResponse->getId());
        $wlPayment->setPaymentProductId($cardPaymentMethod->getPaymentProductId());
        $wlPayment->setAmount($this->getAmount($output));
        $wlPayment->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
    }

    private function addRedirectPaymentMethodData(DataObject $worldlineResponse, PaymentInterface $wlPayment): void
    {
        $output = $worldlineResponse->getPaymentOutput();
        $redirectPaymentMethod = $output->getRedirectPaymentMethodSpecificOutput();
        if (!$redirectPaymentMethod) {
            return;
        }

        $wlPayment->setIncrementId($output->getReferences()->getMerchantReference());
        $wlPayment->setPaymentId($worldlineResponse->getId());
        $wlPayment->setPaymentProductId($redirectPaymentMethod->getPaymentProductId());
        $wlPayment->setAmount($this->getAmount($output));
        $wlPayment->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
    }

    private function addSepaPaymentMethodData(DataObject $worldlineResponse, PaymentInterface $wlPayment): void
    {
        $output = $worldlineResponse->getPaymentOutput();
        $sepaPaymentMethod = $output->getSepaDirectDebitPaymentMethodSpecificOutput();
        if (!$sepaPaymentMethod) {
            return;
        }

        $wlPayment->setIncrementId($output->getReferences()->getMerchantReference());
        $wlPayment->setPaymentId($worldlineResponse->getId());
        $wlPayment->setPaymentProductId($sepaPaymentMethod->getPaymentProductId());
        $wlPayment->setAmount($this->getAmount($output));
        $wlPayment->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
    }

    private function getAmount(DataObject $output): int
    {
        $amount = (int)$output->getAmountOfMoney()->getAmount();
        if ($output->getSurchargeSpecificOutput()) {
            $amount += (int)$output->getSurchargeSpecificOutput()->getSurchargeAmount()->getAmount();
        }

        return $amount;
    }
}
