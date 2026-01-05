<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Data\Form\Element;

class WebhookToggle extends Toggle
{
    protected function getLabelOn(): string
    {
        return (string)__('Automatic (Recommended)');
    }

    protected function getLabelOff(): string
    {
        return (string)__('Manual');
    }

    protected function getAfterToggleHtml(): string
    {
        return $this->getVisualJsHtml();
    }

    private function getVisualJsHtml(): string
    {
        $id = $this->getHtmlId();

        return <<<SCRIPT
        <script>
            (function() {
                const toggleId = "{$id}";

                function updateWebhookVisuals() {
                    const toggle = document.getElementById(toggleId);
                    if (!toggle) return;

                    const isChecked = toggle.checked;

                    const autoComment = document.getElementById('webhook_url_comment_automatic');
                    const manualComment = document.getElementById('webhook_url_comment_manual');
                    if (autoComment) autoComment.style.display = isChecked ? 'block' : 'none';
                    if (manualComment) manualComment.style.display = isChecked ? 'none' : 'block';

                    const copyBtn = document.getElementById('webhook_copy_button');
                    if (copyBtn) {
                        copyBtn.style.display = isChecked ? 'none' : '';
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const toggle = document.getElementById(toggleId);
                    if (toggle) {
                        toggle.addEventListener('change', updateWebhookVisuals);
                        updateWebhookVisuals();
                    }
                });

                updateWebhookVisuals();
            })();
        </script>
SCRIPT;
    }
}
