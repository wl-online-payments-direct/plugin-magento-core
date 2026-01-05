<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Data\Form\Element;

class Toggle extends Checkbox
{
    public function getElementHtml(): string
    {
        $this->setData('style', 'position:absolute; clip:rect(0,0,0,0); overflow:hidden');
        $this->addClass('admin__actions-switch-checkbox');

        if ($this->getData('disabled')) {
            $this->addClass('disabled');
        }

        $html = '<div class="admin__actions-switch" data-role="switcher" style="margin-bottom: 10px;">';
        $html .= parent::getElementHtml();
        $html .= '</div>';

        $html .= $this->getAfterToggleHtml();

        return $html;
    }

    protected function getAfterToggleHtml(): string
    {
        return '';
    }

    protected function getSecondaryLabelHtml(): string
    {
        $html = '<label for="%s" class="admin__actions-switch-label">
            <span class="admin__actions-switch-text" data-text-on="%s" data-text-off="%s"></span>
        </label>';

        return sprintf(
            $html,
            $this->getHtmlId(),
            $this->getLabelOn(),
            $this->getLabelOff()
        );
    }

    protected function getLabelOn(): string
    {
        $config = $this->getData('field_config');
        return isset($config['label_on']) ? (string)__($config['label_on']) : (string)__('Yes');
    }

    protected function getLabelOff(): string
    {
        $config = $this->getData('field_config');
        return isset($config['label_off']) ? (string)__($config['label_off']) : (string)__('No');
    }
}
