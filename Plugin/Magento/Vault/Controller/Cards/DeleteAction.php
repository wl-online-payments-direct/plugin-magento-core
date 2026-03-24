<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Plugin\Magento\Vault\Controller\Cards;

use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Controller\Cards\DeleteAction as MagentoDeleteAction;
use Magento\Vault\Model\PaymentTokenManagement;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Token\DeleteTokenServiceInterface;

class DeleteAction
{
    private const WORLDLINE_PAYMENT_CODE_PREFIX = 'worldline';

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var DeleteTokenServiceInterface
     */
    private $deleteTokenService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PaymentTokenManagement $paymentTokenManagement,
        Session $customerSession,
        DeleteTokenServiceInterface $deleteTokenService,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerSession = $customerSession;
        $this->deleteTokenService = $deleteTokenService;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param MagentoDeleteAction $subject
     *
     * @return void
     */
    public function beforeExecute(MagentoDeleteAction $subject): void
    {
        $publicHash = $subject->getRequest()->getPostValue(PaymentTokenInterface::PUBLIC_HASH);

        if (!$publicHash) {
            return;
        }

        $paymentToken = $this->paymentTokenManagement->getByPublicHash(
            $publicHash,
            $this->customerSession->getCustomerId()
        );

        if (!$paymentToken) {
            return;
        }

        if (strpos($paymentToken->getPaymentMethodCode(), self::WORLDLINE_PAYMENT_CODE_PREFIX) !== 0) {
            return;
        }

        $gatewayToken = $paymentToken->getGatewayToken();
        $storeId = (int) $this->storeManager->getStore()->getId();

        $tokenDetails = json_decode($paymentToken->getTokenDetails() ?? '{}', true);
        $maskedCC = $tokenDetails['maskedCC'] ?? 'unknown';
        $this->logger->debug(
            'Worldline stored card is being deleted.',
            ['masked_cc' => $maskedCC]
        );

        $this->deleteTokenService->execute($gatewayToken, $storeId);
    }
}
