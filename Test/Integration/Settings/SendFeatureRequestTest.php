<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Test\Integration\Settings;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\HttpFactory as HttpRequestFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;
use Worldline\PaymentCore\Controller\Adminhtml\System\Config\SendFeatureRequestFactory;

class SendFeatureRequestTest extends TestCase
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var HttpRequestFactory
     */
    private $httpRequestFactory;

    /**
     * @var SendFeatureRequestFactory
     */
    private $sendFeatureRequestFactory;

    public function setUp(): void
    {
        Bootstrap::getInstance()->loadArea('adminhtml');
        $objectManager = Bootstrap::getObjectManager();
        $this->formKey = $objectManager->get(FormKey::class);
        $this->httpRequestFactory = $objectManager->get(HttpRequestFactory::class);
        $this->sendFeatureRequestFactory = $objectManager->get(SendFeatureRequestFactory::class);
    }

    public function testSendFeatureRequest(): void
    {
        $user = Bootstrap::getObjectManager()->create(User::class)
            ->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $session = Bootstrap::getObjectManager()->get(Session::class);
        $session->setUser($user);

        $params = [
            'form_key' => $this->formKey->getFormKey(),
            'pspid' => 'test_pspid',
            'store_id' => '1',
            'company_name' => 'test_company',
            'contact_email' => 'test@gmail.com',
            'body_message' => 'test_message'
        ];

        $request = $this->httpRequestFactory->create();
        $sendFeatureRequestController = $this->sendFeatureRequestFactory->create(['request' => $request]);

        $sendFeatureRequestController->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $result = $sendFeatureRequestController->execute();

        // validate controller result
        $reflectedResult = new \ReflectionObject($result);
        $jsonProperty = $reflectedResult->getProperty('json');
        $jsonProperty->setAccessible(true);
        $this->assertEquals('{"success":true,"errorMessage":""}', $jsonProperty->getValue($result));
    }
}
