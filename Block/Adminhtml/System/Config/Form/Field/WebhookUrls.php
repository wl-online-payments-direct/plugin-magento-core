<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Escaper;

class WebhookUrls extends Field
{
    private const MAX_WEBHOOK_URLS = 4;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element): string
    {
        $tooltipContent = $element->getTooltip();

        if ($tooltipContent) {
            $element->setTooltip(null);

            $tooltipHtml = <<<HTML
                <div class="tooltip _rich" onclick="return false;">
                    <span class="help"><span></span></span>
                    <div class="tooltip-content">
                        {$tooltipContent}
                    </div>
                </div>
HTML;

            $element->setLabel($element->getLabel() . $tooltipHtml);
        }

        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $id = $element->getHtmlId();
        $name = $element->getName();
        $values = $element->getValue();
        $disabledAttr = $element->getData('disabled') ? ' disabled="disabled"' : '';

        if (!is_array($values)) {
            $values = [];
        }

        while (count($values) < self::MAX_WEBHOOK_URLS) {
            $values[] = '';
        }

        $html = '<div class="additional-webhook-urls-container">';

        for ($i = 0; $i < self::MAX_WEBHOOK_URLS; $i++) {
            $fieldId = $id . '_' . $i;
            $value = $this->escaper->escapeHtmlAttr($values[$i] ?? '');
            $placeholder = __('Optional');

            $html .= <<<HTML
<div class="admin__field" style="margin-bottom: 10px;">
    <div class="admin__field-control">
        <input type="text"
               id="$fieldId"
               name="{$name}[]"
               value="$value"
               class="admin__control-text"
               placeholder="$placeholder"
               style="width: 100%;"
               $disabledAttr />
    </div>
</div>
HTML;
        }

        $html .= '</div>';

        return $html;
    }
}
