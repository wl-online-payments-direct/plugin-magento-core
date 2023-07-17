<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Integration\Settings;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Worldline\PaymentCore\Model\EmailSender;

class PaymentRefusedEmailTest extends TestCase
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->emailSender = $objectManager->get(EmailSender::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoConfigFixture current_store worldline_order_creator/general/sending_payment_refused_emails 0
     * @magentoDbIsolation enabled
     */
    public function testNotify(): void
    {
        $quote = $this->getQuote();
        $this->assertFalse($this->emailSender->sendPaymentRefusedEmail($quote));
    }

    private function getQuote(): CartInterface
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->setOrder(CartInterface::KEY_ENTITY_ID);
        $quoteCollection->getSelect()->limit(1);
        return $quoteCollection->getLastItem();
    }
}
