<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Checkbox as CoreCheckbox;

class Checkbox extends CoreCheckbox
{
    public const PSEUDO_POSTFIX = '_pseudo';

    public function getElementHtml(): string
    {
        $this->setIsChecked((bool) $this->getData('value'));
        $this->setData('after_element_js', $this->getSecondaryLabelHtml() . $this->getJsHtml());

        return '<span style="font-size: 14px">' . parent::getElementHtml() . '</span>';
    }

    public function getButtonLabel(): string
    {
        return $this->getData('field_config')['button_label'] ?? '';
    }

    protected function getJsHtml(): string
    {
        $elementId = $this->getHtmlId();
        $inheritId = $elementId . '_inherit';
        $rowId = 'row_' . $elementId;

        $disabledAttr = $this->getData('disabled') ? 'disabled="disabled"' : '';

        $html = '<input type="hidden" id="%s" value="%s" %s />
        <script>
            (function() {
                const checkbox = document.getElementById("%s");
                const hidden = document.getElementById("%s");
                const row = document.getElementById("%s");
                const inheritId = "%s";

                if (!checkbox || !hidden) return;

                hidden.name = checkbox.name;
                checkbox.name = "";
                checkbox.addEventListener("change", function (event) {
                    checkbox.value = hidden.value = event.target.checked ? "1" : "0";
                });

                if (row) {
                    row.addEventListener("change", function(e) {
                        if (e.target.id === inheritId) {
                            hidden.disabled = e.target.checked;
                        }
                    });
                }
            })();
        </script>';

        return sprintf(
            $html,
            $elementId . self::PSEUDO_POSTFIX,
            $this->getIsChecked() ? '1' : '0',
            $disabledAttr,
            $elementId,
            $elementId . self::PSEUDO_POSTFIX,
            $rowId,
            $inheritId
        );
    }

    protected function getSecondaryLabelHtml(): string
    {
        $html = '<label for="%s" class="admin__field-label">%s</label>';
        return sprintf(
            $html,
            $this->getHtmlId(),
            __($this->getButtonLabel())
        );
    }
}
