<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Worldline\PaymentCore\Model\Config\ConnectionTest\FromAjaxRequest;

class TestConnection extends Action implements HttpPostActionInterface
{
    private const SUCCESS_RESULT = 'OK';

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_PaymentCore::config_worldline';

    /**
     * @var FromAjaxRequest
     */
    private $connectionTester;

    public function __construct(
        Context $context,
        FromAjaxRequest $connectionTester
    ) {
        parent::__construct($context);

        $this->connectionTester = $connectionTester;
    }

    public function execute(): Json
    {
        /** @var Json $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->connectionTester->test();
        } catch (LocalizedException $e) {
            $result = $e->getMessage();
        }

        return $resultPage->setData(
            ($result === self::SUCCESS_RESULT)
                ? ['success' => true]
                : ['success' => false, 'errorMessage' => $result]
        );
    }
}
