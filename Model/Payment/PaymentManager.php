<?php

declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Payment;

use OnlinePayments\Sdk\DataObject;
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

    private function addCardPaymentMethodData(DataObject $worldlineResponse, PaymentInterface $wlPayment): void
    {
        $output = $worldlineResponse->getPaymentOutput();
        $cardPaymentMethod = $output->getCardPaymentMethodSpecificOutput();
        if (!$cardPaymentMethod) {
            return;
        }

        $wlPayment->setIncrementId($output->getReferences()->getMerchantReference());
        $wlPayment->setPaymentId($worldlineResponse->getId());
        $wlPayment->setPaymentProductId($cardPaymentMethod->getPaymentProductId());
        $wlPayment->setCardNumber(trim($cardPaymentMethod->getCard()->getCardNumber(), '*'));
        $wlPayment->setAmount((int) $output->getAmountOfMoney()->getAmount());
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
        $wlPayment->setAmount((int) $output->getAmountOfMoney()->getAmount());
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
        $wlPayment->setAmount((int) $output->getAmountOfMoney()->getAmount());
        $wlPayment->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
    }
}
