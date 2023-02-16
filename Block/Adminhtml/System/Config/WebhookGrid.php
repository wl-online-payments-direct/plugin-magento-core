<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookGrid extends Field
{
    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }

    protected function _prepareLayout(): WebhookGrid
    {
        $this->setTemplate('Worldline_PaymentCore::config/form/field/request_log_grid.phtml');
        return parent::_prepareLayout();
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'label' => __($originalData['label']),
                'html_id' => $element->getHtmlId(),
                'log_url' => $this->_urlBuilder->getUrl('worldline/system/webhooks')
            ]
        );

        return $this->_toHtml();
    }
}
