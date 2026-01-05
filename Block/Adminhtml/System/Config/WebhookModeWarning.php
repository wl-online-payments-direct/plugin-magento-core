<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookModeWarning extends Field
{
    public function render(AbstractElement $element): string
    {
        $elementId = "row_{$element->getHtmlId()}";

        $message = '<strong>' . __('Automatic:') . '</strong> ';
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $message .= __('The URL(s) below will be used for transactions from this store, any webhook URL(s) configured in the merchant portal will be ignored.');
        $message .= '<br>';
        $message .= '<strong>' . __('Manual:') . '</strong> ';
        $message .= __('You are fully responsible for adding your store webhook URL in the merchant portal.');
        $message .= ' <strong>' . __('Failure to do so could result in missing or incomplete orders!') . '</strong>';

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
