<?php
declare(strict_types=1);

namespace Worldline\PaymentCore\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage;

class ContentProcessor
{
    public const FILENAME = 'worldline/debug.log';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Storage $storage,
        Filesystem $filesystem
    ) {
        $this->storage = $storage;
        $this->filesystem = $filesystem;
    }

    /**
     * @return DataObject
     * @throws FileSystemException
     */
    public function process(): DataObject
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::LOG);
        $path = $directory->getAbsolutePath(self::FILENAME);
        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile(self::FILENAME) && !$this->storage->processStorageFile($path))
        ) {
            return $this->getEmptyResultObject();
        }

        $stat = $directory->stat(self::FILENAME);
        $contentLength = $stat['size'];
        $contentModify = $stat['mtime'];
        $content = $directory->readFile(self::FILENAME);

        $resultObject = $this->getEmptyResultObject();

        return $resultObject->setContent($content)
            ->setContentLength($contentLength)
            ->setContentModify($contentModify);
    }

    private function getEmptyResultObject(): DataObject
    {
        $resultObject = new DataObject();
        return $resultObject->setContent('')
            ->setContentLength(0)
            ->setContentModify(time());
    }
}
