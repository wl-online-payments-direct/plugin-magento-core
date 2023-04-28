<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Data\RequestLogInterfaceFactory;
use Worldline\PaymentCore\Logger\Config\ConfigDebugProvider;
use Worldline\PaymentCore\Logger\Config\Source\LogMode;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog as RequestLogResource;

class RequestLogManager
{
    /**
     * @var RequestLogInterfaceFactory
     */
    private $requestLogFactory;

    /**
     * @var RequestLogResource
     */
    private $requestLogResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigDebugProvider
     */
    private $configDebugProvider;

    public function __construct(
        RequestLogInterfaceFactory $requestLogFactory,
        RequestLogResource $requestLogResource,
        LoggerInterface $logger,
        ConfigDebugProvider $configDebugProvider
    ) {
        $this->requestLogFactory = $requestLogFactory;
        $this->requestLogResource = $requestLogResource;
        $this->logger = $logger;
        $this->configDebugProvider = $configDebugProvider;
    }

    public function log(
        string $relativeUriPath,
        int $responseCode,
        string $requestBody = '',
        string $responseBody = ''
    ): void {
        if ($responseCode < 400
            && ($this->configDebugProvider->getLogMode() === LogMode::LOG_ERROR_REQUESTS_ONLY)
        ) {
            return;
        }

        $logRequest = $this->requestLogFactory->create();

        $logRequest->setRequestPath($relativeUriPath);
        $logRequest->setRequestBody($requestBody);
        $logRequest->setResponseBody((string) str_replace([':"', ',"'], [': "', ', "'], $responseBody));
        $logRequest->setResponseCode($responseCode);

        try {
            $this->requestLogResource->save($logRequest);
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}
