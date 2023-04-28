<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger;

use Magento\Framework\Model\AbstractModel;
use Worldline\PaymentCore\Logger\ResourceModel\RequestLog as RequestLogResource;
use Worldline\PaymentCore\Api\Data\RequestLogInterface;

class RequestLog extends AbstractModel implements RequestLogInterface
{
    protected function _construct(): void
    {
        $this->_init(RequestLogResource::class);
    }

    public function getRequestPath(): ?string
    {
        return $this->getData(self::REQUEST_PATH);
    }

    public function setRequestPath(string $requestPath): RequestLogInterface
    {
        $this->setData(self::REQUEST_PATH, $requestPath);
        return $this;
    }

    public function getRequestBody(): ?string
    {
        return $this->getData(self::REQUEST_BODY);
    }

    public function setRequestBody(string $requestBody): RequestLogInterface
    {
        $this->setData(self::REQUEST_BODY, $requestBody);
        return $this;
    }

    public function getResponseBody(): ?string
    {
        return $this->getData(self::RESPONSE_BODY);
    }

    public function setResponseBody(string $responseBody): RequestLogInterface
    {
        $this->setData(self::RESPONSE_BODY, $responseBody);
        return $this;
    }

    public function getResponseCode(): ?string
    {
        return $this->getData(self::RESPONSE_CODE);
    }

    public function setResponseCode(int $responseCode): RequestLogInterface
    {
        $this->setData(self::RESPONSE_CODE, $responseCode);
        return $this;
    }
}
