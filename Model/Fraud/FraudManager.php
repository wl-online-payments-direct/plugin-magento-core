<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Fraud;

use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodSpecificOutput;
use Worldline\PaymentCore\Api\Data\FraudInterface;
use Worldline\PaymentCore\Api\Data\FraudInterfaceFactory;
use Worldline\PaymentCore\Api\Data\PaymentInterface;
use Worldline\PaymentCore\Api\FraudManagerInterface;
use Worldline\PaymentCore\Api\FraudRepositoryInterface;

/**
 * Manager for fraud entity
 */
class FraudManager implements FraudManagerInterface
{
    /**
     * @var FraudInterfaceFactory
     */
    private $fraudEntityFactory;

    /**
     * @var FraudRepositoryInterface
     */
    private $fraudRepository;

    public function __construct(
        FraudInterfaceFactory $fraudEntityFactory,
        FraudRepositoryInterface $fraudRepository
    ) {
        $this->fraudEntityFactory = $fraudEntityFactory;
        $this->fraudRepository = $fraudRepository;
    }

    public function saveFraudInformation(
        DataObject $worldlineResponse,
        PaymentInterface $wlPayment
    ): ?FraudInterface {
        $output = $this->getOutput($worldlineResponse);
        if (!$output) {
            return null;
        }

        $fraudEntity = $this->fraudEntityFactory->create();
        $fraudEntity->setWorldlinePaymentId((int) $wlPayment->getEntityId());

        $fraudResults = $output->getFraudResults();
        if ($fraudResults) {
            $fraudEntity->setResult($fraudResults->getFraudServiceResult());
        }

        if ($output instanceof CardPaymentMethodSpecificOutput
            || $output instanceof MobilePaymentMethodSpecificOutput
        ) {
            $threeDSecureResults = $output->getThreeDSecureResults();
            if ($threeDSecureResults) {
                $fraudEntity->setExemption((string) $threeDSecureResults->getAppliedExemption());
                $fraudEntity->setLiability((string) $threeDSecureResults->getLiability());
                $fraudEntity->setAuthenticationStatus((string) $threeDSecureResults->getAuthenticationStatus());
            }
        }

        return $this->fraudRepository->save($fraudEntity);
    }

    private function getOutput(DataObject $worldlineResponse): ?DataObject
    {
        $output = $worldlineResponse->getPaymentOutput();
        $cardPaymentMethod = $output->getCardPaymentMethodSpecificOutput();
        if ($cardPaymentMethod) {
            return $cardPaymentMethod;
        }

        return $output->getRedirectPaymentMethodSpecificOutput();
    }
}
