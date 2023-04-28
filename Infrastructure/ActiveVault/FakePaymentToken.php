<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Infrastructure\ActiveVault;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;

class FakePaymentToken
{
    public function createVaultToken(string $paymentMethodCode): void
    {
        /** @var PaymentTokenRepositoryInterface $repository */
        $repository = Bootstrap::getObjectManager()->get(PaymentTokenRepositoryInterface::class);
        /** @var PaymentTokenInterface $token */
        $token = Bootstrap::getObjectManager()->create(PaymentTokenInterface::class);
        $token->setCustomerId(1);
        $token->setPaymentMethodCode($paymentMethodCode);
        $token->setPublicHash('fakePublicHash');
        $token->setIsActive(true);
        $token->setIsVisible(true);
        $token->setCreatedAt(strtotime('-1 day'));
        $token->setExpiresAt(strtotime('+1 day'));
        $tokenDetails = ['cc_last4' => '1111', 'cc_exp_year' => '2020', 'cc_exp_month' => '01', 'cc_type' => 'VI'];
        $token->setTokenDetails(json_encode($tokenDetails));
        $repository->save($token);
    }

    public function createVaultSepaToken(string $paymentMethodCode): void
    {
        /** @var PaymentTokenRepositoryInterface $repository */
        $repository = Bootstrap::getObjectManager()->get(PaymentTokenRepositoryInterface::class);
        /** @var PaymentTokenInterface $token */
        $token = Bootstrap::getObjectManager()->create(PaymentTokenInterface::class);
        $token->setCustomerId(1);
        $token->setPaymentMethodCode($paymentMethodCode);
        $token->setPublicHash('fakePublicHash');
        $token->setIsActive(true);
        $token->setIsVisible(true);
        $token->setCreatedAt(strtotime('-1 day'));
        $token->setExpiresAt(strtotime('+1 day'));
        $token->setGatewayToken('exampleMandateReference');
        $tokenDetails = [
            'cc_last4' => '1111',
            'cc_exp_year' => '2020',
            'cc_exp_month' => '01',
            'cc_type' => 'VI',
            'payment_product_id' => PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID
        ];
        $token->setTokenDetails(json_encode($tokenDetails));
        $repository->save($token);
    }
}
