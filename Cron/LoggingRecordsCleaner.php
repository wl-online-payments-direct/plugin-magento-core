<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Cron;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog;
use Worldline\PaymentCore\Model\Config\WorldlineConfig;
use Worldline\PaymentCore\Model\Log\ResourceModel\Log;

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
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Worldline\PaymentCore\Model\Config\WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var RequestLog
     */
    private $requestLog;

    public function __construct(
        Log $logResource,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        RequestLog $requestLog
    ) {
        $this->logResource = $logResource;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->worldlineConfig = $worldlineConfig;
        $this->requestLog = $requestLog;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $days = $this->worldlineConfig->getLoggingLifetime();
        if ($days === null) {
            return;
        }

        $offset = (int)$days * self::SEC_IN_DAY;
        $date = $this->dateTime->formatDate($this->timezone->scopeTimeStamp() - $offset);

        try {
            $this->logResource->clearRecordsByDate($date);
            $this->requestLog->clearRecordsByDate($date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
