<?php

namespace App\EventListener;

use Vich\UploaderBundle\Event\Event;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;

class ImageResizeListener
{
    private $filterManager;
    private $logger;

    public function __construct(FilterManager $filterManager, LoggerInterface $logger)
    {
        $this->filterManager = $filterManager;
        $this->logger = $logger;
    }

    /**
     * Handles the post upload event to process the uploaded image.
     *
     * @param Event $event The event triggered after an image is uploaded.
     */
    public function onPostUpload(Event $event): void
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        // Checks if image name is set
        if (null === $object->getImageName()) {
            $this->logger->warning('Image name is null.');
            return;
        }

        $imagePath = $mapping->getUploadDir($object) . '/' . $object->getImageName();

        // Checks if file exists before applying filter
        if (!file_exists($imagePath)) {
            $this->logger->error('File does not exist: ' . $imagePath);
            return;
        }

        try {
            // Apply the filter to create the resized image
            $this->filterManager->applyFilter($imagePath, 'thumb_294x294');
        } catch (\Exception $e) {
            $this->logger->error('Error applying filter: ' . $e->getMessage());
        }
    }
}
