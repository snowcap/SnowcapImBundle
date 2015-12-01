<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Metadata\MetadataFactoryInterface;
use Snowcap\ImBundle\Manager as ImManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Event listener for Doctrine entities to evualuate and execute ImBundle annotations
 */
class MogrifySubscriber implements EventSubscriber
{
    /**
     * @var \Metadata\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Snowcap\ImBundle\Manager
     */
    private $imManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param MetadataFactoryInterface  $metadataFactory
     * @param ImManager                 $imManager          The ImBundle manager instance
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, ImManager $imManager)
    {
        $this->metadataFactory = $metadataFactory;
        $this->imManager = $imManager;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('prePersist', 'preFlush');
    }

    /**
     * @param PreFlushEventArgs $ea
     */
    public function preFlush(PreFlushEventArgs $ea)
    {
        $unitOfWork = $ea->getEntityManager()->getUnitOfWork();
        $entityMaps = $unitOfWork->getIdentityMap();

        foreach ($entityMaps as $entities) {
            foreach ($entities as $entity) {
                foreach ($this->metadataFactory->getMetadataForClass(get_class($entity))->propertyMetadata as $file) {
                    $this->mogrify($entity, $file);
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $ea
     */
    public function prePersist(LifecycleEventArgs $ea)
    {
        $entity = $ea->getEntity();
        foreach ($this->metadataFactory->getMetadataForClass(get_class($entity))->propertyMetadata as $file) {
            $this->mogrify($entity, $file);
        }
    }

    /**
     * @param $entity
     * @param $file
     */
    private function mogrify($entity, $file)
    {
        $uploadedFile = $this->propertyAccessor->getValue($entity, $file->name);
        if ($uploadedFile instanceof UploadedFile) {
            $this->imManager->mogrify($file->params, $uploadedFile->getPathName());
        }
    }
}
