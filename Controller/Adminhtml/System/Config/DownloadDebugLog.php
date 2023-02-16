<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\FileSystemException;
use Worldline\PaymentCore\Logger\ContentProcessor;

class DownloadDebugLog extends Action implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var ContentProcessor
     */
    private $contentProcessor;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        ContentProcessor $contentProcessor
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * @return Raw
     * @throws FileSystemException
     */
    public function execute(): Raw
    {
        $contentObject = $this->contentProcessor->process();

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $contentObject->getContentLength())
            ->setHeader('Content-Disposition', 'attachment; filename="debug.log"', true)
            ->setHeader('Last-Modified', date('r', $contentObject->getContentModify()));
        $resultRaw->setContents($contentObject->getContent());

        return $resultRaw;
    }
}
