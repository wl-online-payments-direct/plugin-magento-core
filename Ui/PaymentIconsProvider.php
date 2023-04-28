<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Ui;

use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source as AssetSource;
use Worldline\PaymentCore\Api\Ui\PaymentIconsProviderInterface;

class PaymentIconsProvider implements PaymentIconsProviderInterface
{
    public const REGEXP_ATTR_VIEWBOX =
        '/viewBox=[\'"](?<startX>\d+) (?<startY>\d+) (?<width>[\d\.]+) (?<height>[\d\.]+)[\'"]/i';

    /**
     * @var PaymentProductsProvider
     */
    private $paymentProductsProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AssetSource
     */
    private $assetSource;

    /**
     * @var array
     */
    private $icons = [];

    public function __construct(
        PaymentProductsProvider $paymentProductsProvider,
        Repository $assetRepo,
        RequestInterface $request,
        AssetSource $assetSource
    ) {
        $this->paymentProductsProvider = $paymentProductsProvider;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->assetSource = $assetSource;
    }

    public function getIconById(?int $id, int $storeId): array
    {
        return $this->getIcons($storeId)[$id] ?? [];
    }

    public function getIcons(int $storeId): array
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $paymentProducts = $this->paymentProductsProvider->getPaymentProducts($storeId);
        foreach ($paymentProducts as $paymentProductId => $paymentProductData) {
            $this->generateIconById($paymentProductId, $storeId, $paymentProductData);
        }

        return $this->icons;
    }

    private function generateIconById(?int $id, int $storeId, ?array $data = null): void
    {
        if (empty($data)) {
            $data = $this->paymentProductsProvider->getPaymentProducts($storeId)[$id] ?? [];
        }

        $asset = $this->createAsset(
            'Worldline_PaymentCore::images/pm/pp_logo_' . $id . '.svg',
            [Area::PARAM_AREA => Area::AREA_FRONTEND]
        );

        if (!$asset) {
            return;
        }

        $placeholder = $this->assetSource->findSource($asset);
        if ($placeholder) {
            [$width, $height] = $this->getDimensions($asset);
            $this->icons[$id] = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height,
                'title' => $data['label'] ?? '',
                'method' => $data['method'] ?? ''
            ];
        }
    }

    /**
     * Create a file asset that's subject of fallback system.
     *
     * @param string $fileId
     * @param array $params
     * @return File|null
     */
    public function createAsset(string $fileId, array $params = []): ?File
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            $result =  $this->assetRepo->createAsset($fileId, $params);
        } catch (LocalizedException $e) {
            $result = null;
        }

        return $result;
    }

    public function getDimensions(?File $asset = null): array
    {
        if ($asset === null) {
            return [0, 0];
        }

        if ($this->isSvg($asset)) {
            preg_match(self::REGEXP_ATTR_VIEWBOX, $asset->getContent(), $viewBox);
            $width = (int) $viewBox['width'];
            $height = (int) $viewBox['height'];
        } else {
            $size = getimagesizefromstring($asset->getContent());
            $width = (int) $size[0];
            $height = (int) $size[1];
        }

        return [$width, $height];
    }

    private function isSvg(File $asset): bool
    {
        return (bool)preg_match('/\.svg$/i', $asset->getSourceFile());
    }
}
