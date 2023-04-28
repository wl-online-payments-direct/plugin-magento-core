<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class TestConnection extends Field
{
    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Context $context,
        Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
    }

    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }

    protected function _prepareLayout(): TestConnection
    {
        parent::_prepareLayout();
        $this->setTemplate('Worldline_PaymentCore::config/form/field/testconnection.phtml');
        return $this;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $websiteId = $this->getRequest()->getParam(ScopeInterface::SCOPE_WEBSITE);
        $urlParams = ($websiteId !== null) ? [ScopeInterface::SCOPE_WEBSITE => (int) $websiteId] : [];

        $this->addData(
            [
                'label' => __($originalData['label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('worldline/system_config/testconnection', $urlParams),
                'field_mapping' => str_replace('"', '\\"', $this->serializer->serialize($this->getFieldMapping()))
            ]
        );

        return $this->_toHtml();
    }

    private function getFieldMapping(): array
    {
        return [
            'api_key' => 'worldline_connection_connection_api_key',
            'api_key_prod' => 'worldline_connection_connection_api_key_prod',
            'api_secret' => 'worldline_connection_connection_api_secret',
            'api_secret_prod' => 'worldline_connection_connection_api_secret_prod',
            'testing_api_url' => 'worldline_connection_connection_testing_api_url',
            'production_api_url' => 'worldline_connection_connection_production_api_url',
            'environment_mode' => 'worldline_connection_connection_environment_mode',
            'merchant_id' => 'worldline_connection_connection_merchant_id',
            'merchant_id_prod' => 'worldline_connection_connection_merchant_id_prod'
        ];
    }
}
