<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\Model\Log\ResourceModel\Log;
use Worldline\PaymentCore\Model\Webhook\ResourceModel\Webhook as WebhookResource;

class LoggingRecordsCleaner
{
    public const SEC_IN_DAY = 86400;

    /**
     * @var Log
     */
    private $logResource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var RequestLog
     */
    private $requestLog;

    /**
     * @var WebhookResource
     */
    private $webhookResource;

    public function __construct(
        Log $logResource,
        DateTime $dateTime,
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        RequestLog $requestLog,
        WebhookResource $webhookResource
    ) {
        $this->logResource = $logResource;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->worldlineConfig = $worldlineConfig;
        $this->requestLog = $requestLog;
        $this->webhookResource = $webhookResource;
    }

    public function execute(): void
    {
        $days = $this->worldlineConfig->getLoggingLifetime();
        if ($days === null) {
            return;
        }

        $offset = (int)$days * self::SEC_IN_DAY;
        $date = $this->dateTime->gmtDate(null, $this->dateTime->gmtTimestamp() - $offset);

        try {
            $this->logResource->clearRecordsByDate($date);
            $this->requestLog->clearRecordsByDate($date);
            $this->webhookResource->clearRecordsByDate($date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
