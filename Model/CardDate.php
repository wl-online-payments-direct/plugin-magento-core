<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model;

use DateInterval;
use DateTime;
use DateTimeZone;
use Magento\Framework\Serialize\Serializer\Json;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use Worldline\PaymentCore\Api\CardDateInterface;

class CardDate implements CardDateInterface
{
    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSO
     * @return string
     * @throws \Exception
     */
    public function getExpirationDateAt(CardPaymentMethodSpecificOutput $cardPaymentMethodSO): string
    {
        $card = $cardPaymentMethodSO->getCard();
        $expirationDateAt = $this->processDate($card->getExpiryDate());
        $expirationDateAt->add(new DateInterval('P1M'));
        return $expirationDateAt->format('Y-m-d 00:00:00');
    }

    /**
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSO
     * @return string
     * @throws \Exception
     */
    public function getExpirationDate(CardPaymentMethodSpecificOutput $cardPaymentMethodSO): string
    {
        $card = $cardPaymentMethodSO->getCard();
        return $this->processDate($card->getExpiryDate())->format('m/Y');
    }

    /**
     * @param string $date
     * @return DateTime
     * @throws \Exception
     */
    public function processDate(string $date): DateTime
    {
        return new DateTime(
            mb_substr($date, -2)
            . '-'
            . mb_substr($date, 0, 2)
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
    }

    public function convertDetailsToJSON(array $details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }
}
