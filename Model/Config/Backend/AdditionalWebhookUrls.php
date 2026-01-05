<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Laminas\Uri\UriFactory;

class AdditionalWebhookUrls extends Value
{
    private const MAX_URLS = 4;
    private const MAX_URL_LENGTH = 325;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param SerializerInterface $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SerializerInterface $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    public function beforeSave()
    {
        $value = $this->getValue();

        if (empty($value)) {
            $this->setValue($this->serializer->serialize([]));
            return parent::beforeSave();
        }

        if (is_array($value)) {
            $urls = array_filter($value, function ($url) {
                return !empty(trim($url));
            });

            if (count($urls) > self::MAX_URLS) {
                throw new LocalizedException(
                    __('You can add a maximum of %1 additional webhook URLs.', self::MAX_URLS)
                );
            }

            foreach ($urls as $url) {
                $this->validateUrl(trim($url));
            }

            $urls = array_values($urls);
            $this->setValue($this->serializer->serialize($urls));
        } else {
            $url = trim($value);
            if (!empty($url)) {
                $this->validateUrl($url);
                $this->setValue($this->serializer->serialize([$url]));
            } else {
                $this->setValue($this->serializer->serialize([]));
            }
        }

        return parent::beforeSave();
    }

    protected function _afterLoad()
    {
        $value = $this->getValue();

        if (!empty($value)) {
            try {
                $unserialized = $this->serializer->unserialize($value);
                $this->setValue(is_array($unserialized) ? $unserialized : []);
            } catch (\Exception $e) {
                $this->setValue([]);
            }
        } else {
            $this->setValue([]);
        }

        return parent::_afterLoad();
    }

    private function validateUrl(string $url): void
    {
        if (strlen($url) > self::MAX_URL_LENGTH) {
            throw new LocalizedException(
                __('Webhook URL cannot exceed %1 characters.', self::MAX_URL_LENGTH)
            );
        }

        if (substr($url, 0, 8) !== 'https://') {
            throw new LocalizedException(
                __('Webhook URL must start with "https://": %1', $url)
            );
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new LocalizedException(
                __('Invalid URL format: %1', $url)
            );
        }

        $host = UriFactory::factory($url)->getHost();

        if (!$host || !str_contains($host, '.')) {
            throw new LocalizedException(
                __('Webhook URL must contain a valid domain (e.g., example.com): %1', $url)
            );
        }
    }
}
