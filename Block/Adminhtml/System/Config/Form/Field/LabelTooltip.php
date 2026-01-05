<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class LabelTooltip extends Field
{
    /**
     * Override render to move the tooltip from the value cell to the label cell.
     */
    public function render(AbstractElement $element): string
    {
        $tooltipContent = $element->getTooltip();

        if ($tooltipContent) {
            $element->setTooltip(null);

            $tooltipHtml = <<<HTML
                <div class="tooltip _rich" onclick="return false;">
                    <span class="help"><span></span></span>
                    <div class="tooltip-content">{$tooltipContent}</div>
                </div>
HTML;

            $element->setLabel($element->getLabel() . $tooltipHtml);
        }

        return parent::render($element);
    }
}
