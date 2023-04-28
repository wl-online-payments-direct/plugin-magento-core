<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\Webhook\Grid;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Button for Clear Webhook Logs
 */
class ClearLogButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var FormKey
     */
    private $formKey;

    public function __construct(UrlInterface $urlBuilder, FormKey $formKey)
    {
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
    }

    public function getButtonData(): array
    {
        return [
            'label' => __('Clear log'),
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure you want to do this? All webhook data will be removed.'
            ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
            'sort_order' => 10,
        ];
    }

    public function getDeleteUrl(): string
    {
        return $this->urlBuilder->getUrl('*/*/CleanWebhooks', ['form_key' => $this->formKey->getFormKey()]);
    }
}
