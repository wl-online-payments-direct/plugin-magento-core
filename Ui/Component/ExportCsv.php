<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Ui\Component;

use Magento\Ui\Component\ExportButton;

/**
 * Add only csv export option. The original task was to remove the Excel XML option.
 */
class ExportCsv extends ExportButton
{
    public function prepare(): void
    {
        $config = $this->getData('config');
        if (empty($config['options_custom'])) {
            return;
        }

        $options = [];
        $context = $this->getContext();
        foreach ($config['options_custom'] as $option) {
            $additionalParams = $this->getAdditionalParams($config, $context);
            $option['url'] = $this->urlBuilder->getUrl($option['url'], $additionalParams);
            $options[] = $option;
        }
        $config['options'] = $options;
        $this->setData('config', $config);
    }
}
