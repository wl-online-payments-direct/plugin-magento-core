<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\OnlinePayments\Sdk;

use OnlinePayments\Sdk\Communication\MetadataProvider;
use OnlinePayments\Sdk\CommunicatorConfiguration;

class RequestHeaderGenerator extends MetadataProvider
{
    /**
     * @var array
     */
    private $trackerData = [];

    /**
     * @var CommunicatorConfiguration
     */
    private $communicatorConfig;

    public function __construct(CommunicatorConfiguration $communicatorConfiguration)
    {
        parent::__construct($communicatorConfiguration);
        $this->communicatorConfig = $communicatorConfiguration;
    }

    public function setTrackerData(array $trackerData): void
    {
        $this->trackerData = $trackerData;
    }

    public function getServerMetaInfoValue(): string
    {
        $serverMetaInfo = $this->trackerData;

        $serverMetaInfo['platformIdentifier'] = sprintf('%s; php version %s', php_uname(), PHP_VERSION);
        $serverMetaInfo['sdkIdentifier'] = 'PHPServerSDK/v' . static::SDK_VERSION;
        $serverMetaInfo['sdkCreator'] = 'Ingenico';

        $integrator = $this->communicatorConfig->getIntegrator();
        if ($integrator) {
            $serverMetaInfo['integrator'] = $integrator;
        }

        $shoppingCartExtension = $this->communicatorConfig->getShoppingCartExtension();
        if ($shoppingCartExtension) {
            $serverMetaInfo['shoppingCartExtension'] = $shoppingCartExtension->toObject();
        }

        // the sdkIdentifier contains a /. Without the JSON_UNESCAPED_SLASHES, this is turned to \/ in JSON.
        return base64_encode(json_encode($serverMetaInfo, JSON_UNESCAPED_SLASHES));
    }
}
