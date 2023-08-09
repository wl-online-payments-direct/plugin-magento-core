<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\DeclinedPaymentException;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\ClientProviderInterface;
use Worldline\PaymentCore\Api\Config\WorldlineConfigInterface;
use Worldline\PaymentCore\Api\Service\Payment\CreatePaymentServiceInterface;

/**
 * @link https://support.direct.ingenico.com/en/documentation/api/reference/#operation/CreatePaymentApi
 */
class CreatePaymentService implements CreatePaymentServiceInterface
{
    /**
     * @var WorldlineConfigInterface
     */
    private $worldlineConfig;

    /**
     * @var ClientProviderInterface
     */
    private $modelClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        WorldlineConfigInterface $worldlineConfig,
        ClientProviderInterface $modelClient,
        LoggerInterface $logger
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
        $this->logger = $logger;
    }

    /**
     * Create payment
     *
     * @param CreatePaymentRequest $request
     * @param int|null $storeId
     * @return CreatePaymentResponse
     * @throws LocalizedException
     */
    public function execute(CreatePaymentRequest $request, ?int $storeId = null): CreatePaymentResponse
    {
        try {
            return $this->modelClient->getClient($storeId)
                ->merchant($this->worldlineConfig->getMerchantId($storeId))
                ->payments()
                ->createPayment($request);
        } catch (DeclinedPaymentException $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('Your payment has been refused, please try again.'));
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('CreatePaymentApi request has failed. Please contact the provider.'));
        }
    }
}
