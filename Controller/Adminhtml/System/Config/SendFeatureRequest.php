<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Model\FeatureRequestBuilder;

class SendFeatureRequest extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_PaymentCore::config_worldline';

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var FeatureRequestBuilder
     */
    private $featureRequestBuilder;

    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        JsonFactory $resultJsonFactory,
        FeatureRequestBuilder $featureRequestBuilder
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->featureRequestBuilder = $featureRequestBuilder;
    }

    public function execute(): Json
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultJsonFactory->create()->setData($result);
        }

        $pspid = $this->getRequest()->getParam('pspid');
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $companyName = $this->getRequest()->getParam('company_name');
        $contactEmail = $this->getRequest()->getParam('contact_email');
        $bodyMessage = $this->getRequest()->getParam('body_message');

        try {
            $this->featureRequestBuilder->build($storeId, $companyName, $bodyMessage, $contactEmail, $pspid);
            $result['success'] = true;
        } catch (LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
