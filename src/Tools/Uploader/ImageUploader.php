<?php

namespace Tools\Uploader;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImageUploader
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Media Directory object (writable).
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    const FILE_DIR = 'nonamespace';

    /**
     * @param UploaderFactory $uploaderFactory
     * @param BackendModelFactory $backendModelFactory
     * @param MetadataProvider $metadataProvider
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function saveToTmp($fileId)
    {
        try {
            $result = $this->save($fileId, $this->getAbsoluteTmpMediaPath());
            $result['url'] = $this->getTmpMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $result;
    }

    protected function save($fileId, $destination)
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowCreateFolders(true);
        $uploader->setAllowedExtensions($this->allowedExt);

        $result = $uploader->save($destination);
        unset($result['path']);

        return $result;
    }

    protected function getTmpMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'tmp/' . self::FILE_DIR . '/' . $this->prepareFile($file);
    }

    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    protected function getAbsoluteTmpMediaPath()
    {
        return $this->mediaDirectory->getAbsolutePath('tmp/' . self::FILE_DIR);
    }
}
