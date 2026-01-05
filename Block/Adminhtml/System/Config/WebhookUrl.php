<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookUrl extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $webhookUrl = $this->getBaseUrl() . 'worldline/webhook';
        $label = __('Webhook URL');
        $comment_automatic = __('This is your store webhook URL, it will be sent with each transaction');
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $comment_manual = __('This is your store webhook URL, you must add it in the merchant portal. Use the "copy" icon to avoid errors.');
        $elementId = "row_{$element->getHtmlId()}";
        $copyButtonLabel = __('Copy');
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $tooltipContent = __('This is your store\'s unique address for receiving payment notifications. The plugin listens at this URL for real-time status updates to create and update your orders accordingly.');
        $copyButtonId = "webhook_copy_button";
        $commentAutomaticId = "webhook_url_comment_automatic";
        $commentManualId = "webhook_url_comment_manual";

        return <<<HTML
<tr id="$elementId">
    <td class="label">
        <label for="$elementId">
            <span data-config-scope="[GLOBAL]">
                $label
                <div class="tooltip _rich" onclick="return false;">
                    <span class="help"><span></span></span>
                    <div class="tooltip-content">{$tooltipContent}</div>
                </div>
            </span>
        </label>
    </td>
    <td class="value">
        <input disabled="disabled" value="$webhookUrl" type="text" style="float:left; width: 80%;">
        <button id="$copyButtonId"
                style="float:left;"
                onclick="navigator.clipboard.writeText('$webhookUrl');"
                type="button">$copyButtonLabel</button>
        <br><br>
        <p class="note" id="$commentAutomaticId" style="display: none;">
            <span>$comment_automatic</span>
        </p>
        <p class="note" id="$commentManualId" style="display: none;">
            <span>$comment_manual</span>
        </p>
    </td>
</tr>
HTML;
    }
}
