<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Worldline\PaymentCore\Block\Adminhtml\System\Config\Info\VersionProvider;

class Info extends Field
{
    public const SEND_FEATURE_REQUEST = 'worldline/system_config/sendFeatureRequest';

    private const ACCOUNT_LINK = 'https://signup.direct.preprod.worldline-solutions.com/';
    private const SALES_LINK = 'https://worldline.com/en/home/solutions/online-payments/wl-online-payments.html';
    private const SUPPORT_SITE_LINK = 'https://docs.direct.worldline-solutions.com/en/index';
    private const SUPPORT_TEAM_LINK = 'https://docs.direct.worldline-solutions.com/en/about/contact/index';
    private const MAGENTO_DOC_LINK
        = 'https://docs.direct.worldline-solutions.com/en/integration/how-to-integrate/plugins/magento';
    private const GITHUB_LINK = 'https://github.com/wl-online-payments-direct/plugin-magento/releases/latest/';

    /**
     * @var VersionProvider
     */
    private $versionProvider;

    public function __construct(Context $context, VersionProvider $versionProvider, array $data = [])
    {
        parent::__construct($context, $data);
        $this->versionProvider = $versionProvider;
    }

    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }

    public function getFeatureRequestUrl(
        ?string $route = self::SEND_FEATURE_REQUEST,
        ?array $params = []
    ): string {
        return $this->getUrl($route, $params);
    }

    protected function _prepareLayout(): Field
    {
        parent::_prepareLayout();
        $this->setTemplate('Worldline_PaymentCore::config/form/field/info.phtml');
        return $this;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->addData(
            [
                'logo_url' => $this->getLogoUrl(),
                'account_url' => self::ACCOUNT_LINK,
                'support_site_url' => self::SUPPORT_SITE_LINK,
                'support_team_url' => self::SUPPORT_TEAM_LINK,
                'magento_doc_url' => self::MAGENTO_DOC_LINK,
                'sales_team_url' => self::SALES_LINK,
                'git_hub_url' => self::GITHUB_LINK,
                'html_id' => $element->getHtmlId(),
                'current_version' => $this->versionProvider->getCurrentVersion(),
                'latest_version' => $this->versionProvider->getLatestVersion(),
            ]
        );

        return $this->_toHtml();
    }

    private function getLogoUrl(): string
    {
        return $this->getViewFileUrl(
            'Worldline_PaymentCore::images/logo-config-section.png',
            ['area'  => Area::AREA_ADMINHTML]
        );
    }
}
