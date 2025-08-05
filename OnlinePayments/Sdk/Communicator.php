<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\OnlinePayments\Sdk;

use OnlinePayments\Sdk\CallContext;
use OnlinePayments\Sdk\Communicator as IngenicoCommunicator;
use OnlinePayments\Sdk\CommunicatorConfiguration;
use OnlinePayments\Sdk\Communication\Connection;
use OnlinePayments\Sdk\Domain\DataObject;
use OnlinePayments\Sdk\Communication\RequestObject;
use OnlinePayments\Sdk\Communication\ResponseBuilder;
use OnlinePayments\Sdk\Communication\ResponseClassMap;
use OnlinePayments\Sdk\ExceptionFactory;
use OnlinePayments\Sdk\ResponseException;
use UnexpectedValueException;
use Worldline\PaymentCore\Logger\RequestLogManager;
use Worldline\PaymentCore\Model\TrackerDataProvider;
use OnlinePayments\Sdk\Authentication\V1HmacAuthenticator;

/**
 * @core
 *
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Communicator extends IngenicoCommunicator
{
    /**
     * @var TrackerDataProvider
     */
    private $trackerDataProvider;

    /**
     * @var CommunicatorConfiguration
     */
    private $communicatorConfiguration;

    /**
     * @var RequestHeaderGeneratorFactory
     */
    private $requestHeaderGeneratorFactory;

    /**
     * @var RequestLogManager
     */
    private $requestLogManager;

    /**
     * @var Connection
     */
    private $connection;

    /** @var ExceptionFactory|null */
    private $responseExceptionFactory = null;

    public function __construct(
        Connection $connection,
        CommunicatorConfiguration $communicatorConfiguration,
        TrackerDataProvider $trackerDataProvider,
        RequestHeaderGeneratorFactory $requestHeaderGeneratorFactory,
        RequestLogManager $requestLogManager
    ) {
        parent::__construct($communicatorConfiguration, new V1HmacAuthenticator($communicatorConfiguration), $connection);

        $this->connection = $connection;
        $this->trackerDataProvider = $trackerDataProvider;
        $this->communicatorConfiguration = $communicatorConfiguration;
        $this->requestHeaderGeneratorFactory = $requestHeaderGeneratorFactory;
        $this->requestLogManager = $requestLogManager;
    }

    public function buildRequestUri(
        string $relativeUriPath,
        ?RequestObject $requestParameters
    ): string {
        return $this->getRequestUri($relativeUriPath, $requestParameters);
    }

    /**
     * @see \OnlinePayments\Sdk\Communicator::get()
     *
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject|null
     * @throws ResponseException
     */
    public function get(
        ResponseClassMap $responseClassMap,
                         $relativeUriPath,
                         $clientMetaInfo = '',
        RequestObject $requestParameters = null,
        CallContext $callContext = null
    ) {
        $relativeUriPathWithRequestParameters = $this->getRelativeUriPathWithRequestParameters($relativeUriPath, $requestParameters);
        $requestHeaders = $this->getRequestHeaders('GET', $relativeUriPathWithRequestParameters, null, $clientMetaInfo, $callContext);

        $responseBuilder = new ResponseBuilder();
        $responseHandler = function ($httpStatusCode, $data, $headers) use ($responseBuilder) {
            $responseBuilder->setHttpStatusCode($httpStatusCode);
            $responseBuilder->setHeaders($headers);
            $responseBuilder->appendBody($data);
        };

        $this->getConnection()->get(
            $this->communicatorConfiguration->getApiEndpoint() . $relativeUriPathWithRequestParameters,
            $requestHeaders,
            $responseHandler,
            $this->communicatorConfiguration->getProxyConfiguration()
        );
        $connectionResponse = $responseBuilder->getResponse();
        $this->updateCallContext($connectionResponse, $callContext);
        $response = $this->getResponseFactory()->createResponse($connectionResponse, $responseClassMap);
        $httpStatusCode = $connectionResponse->getHttpStatusCode();

        $this->requestLogManager->log(
            (string) $relativeUriPathWithRequestParameters,
            (int) $httpStatusCode,
            '',
            (string) $connectionResponse->getBody()
        );

        if ($httpStatusCode >= 400) {
            throw $this->getResponseExceptionFactory()->createException($httpStatusCode, $response, $callContext);
        }
        return $response;
    }

    /**
     * @see \OnlinePayments\Sdk\Communicator::post()
     *
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param DataObject|null $requestBodyObject
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject|null
     * @throws \Exception
     */
    public function post(
        ResponseClassMap $responseClassMap,
                         $relativeUriPath,
                         $clientMetaInfo = '',
                         $requestBodyObject = null,
        RequestObject $requestParameters = null,
        CallContext $callContext = null
    ) {
        $relativeUriPathWithRequestParameters = $this->getRelativeUriPathWithRequestParameters($relativeUriPath, $requestParameters);
        if ($requestBodyObject instanceof DataObject || is_null($requestBodyObject)) {
            $contentType = static::MIME_APPLICATION_JSON;
            $requestBody = $requestBodyObject ? $requestBodyObject->toJson() : '';
        } else {
            throw new UnexpectedValueException('Unsupported request body');
        }
        $requestHeaders = $this->getRequestHeaders('POST', $relativeUriPathWithRequestParameters, $contentType, $clientMetaInfo, $callContext);

        $responseBuilder = new ResponseBuilder();
        $responseHandler = function ($httpStatusCode, $data, $headers) use ($responseBuilder) {
            $responseBuilder->setHttpStatusCode($httpStatusCode);
            $responseBuilder->setHeaders($headers);
            $responseBuilder->appendBody($data);
        };

        $this->getConnection()->post(
            $this->communicatorConfiguration->getApiEndpoint() . $relativeUriPathWithRequestParameters,
            $requestHeaders,
            $requestBody,
            $responseHandler,
            $this->communicatorConfiguration->getProxyConfiguration()
        );
        $connectionResponse = $responseBuilder->getResponse();
        $this->updateCallContext($connectionResponse, $callContext);
        $response = $this->getResponseFactory()->createResponse($connectionResponse, $responseClassMap);
        $httpStatusCode = $connectionResponse->getHttpStatusCode();

        $this->requestLogManager->log(
            (string) $relativeUriPath,
            (int) $httpStatusCode,
            (string) $requestBody,
            (string) $connectionResponse->getBody()
        );

        if ($httpStatusCode >= 400) {
            throw $this->getResponseExceptionFactory()->createException($httpStatusCode, $response, $callContext);
        }
        return $response;
    }

    /** @return ExceptionFactory */
    private function getResponseExceptionFactory()
    {
        if (is_null($this->responseExceptionFactory)) {
            $this->responseExceptionFactory = new ExceptionFactory();
        }
        return $this->responseExceptionFactory;
    }
}