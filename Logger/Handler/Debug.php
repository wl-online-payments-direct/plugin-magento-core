<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger\Handler;

use Magento\Framework\Filesystem\Driver\File;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Worldline\PaymentCore\Api\Data\LogInterfaceFactory;
use Worldline\PaymentCore\Api\LogRepositoryInterface;
use Worldline\PaymentCore\Model\Log\Log;

class Debug extends StreamHandler
{
    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    public function __construct(
        File                   $filesystem,
        LogRepositoryInterface $logRepository,
        LogInterfaceFactory    $logFactory
    )
    {
        $this->filesystem = $filesystem;
        parent::__construct(BP . DIRECTORY_SEPARATOR . '/var/log/worldline/debug.log');

        $this->setFormatter(new LineFormatter(null, null, true));
        $this->logRepository = $logRepository;
        $this->logFactory = $logFactory;
    }

    protected function write($record): void
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);

        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);

        $this->saveLogToDb($record);
    }

    private function saveLogToDb($record): void
    {
        $callableName = 'toArray';
        if (is_callable($record, true, $callableName)) {
            $content = var_export($record->toArray(), true);
        } else {
            $content = var_export($record, true);
        }

        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->setContent($content);
        $this->logRepository->save($log);
    }
}
