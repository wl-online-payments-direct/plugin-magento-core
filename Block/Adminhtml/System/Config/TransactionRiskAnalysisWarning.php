<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TransactionRiskAnalysisWarning extends Field
{
    public function render(AbstractElement $element): string
    {
        $elementId = "row_{$element->getHtmlId()}";

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $message = __('Please ensure that Transaction Risk Analysis (TRA) is enabled with your acquirer before enabling this option. If not, all card transactions may be blocked.');

        return <<<HTML
<tr id="$elementId">
    <td class="label"></td>
    <td class="value">
        <p class="message message-warning">
            $message
        </p>
    </td>
</tr>
HTML;
    }
}
