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
use Metadata\MetadataFactoryInterface;
use Snowcap\ImBundle\Manager as ImManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Snowcap\ImBundle\Doctrine\Metadata\ConvertMetadata;

/**
 * Event listener for Doctrine entities to evualuate and execute Convert and ConvertMultiple annotations
 */
class ConvertSubscriber implements EventSubscriber
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

    private function recomputeChangeSet($event)
    {
        $object = $event->getEntity();

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($object));
        $uow->recomputeSingleEntityChangeSet($metadata, $object);

        return $this;
    }

    /**
     * @param PreFlushEventArgs $ea
     */
    public function preFlush(PreFlushEventArgs $ea)
    {
        
    }

    /**
     * @param LifecycleEventArgs $ea
     */
    public function prePersist(LifecycleEventArgs $ea)
    {
        $entity = $ea->getEntity();
        foreach ($this->metadataFactory->getMetadataForClass(get_class($entity))->propertyMetadata as $propertyMetadata) {
            if($propertyMetadata instanceof ConvertMetadata) {
                $this->convert($entity, $propertyMetadata);
            }
        }
        
    }

    /**
     * @param $entity
     * @param $propertyMetadata
     */
    private function convert($entity, $propertyMetadata)
    {
        $file = $this->propertyAccessor->getValue($entity, $propertyMetadata->name);
        if ($file instanceof \SplFileInfo) {
            foreach ($propertyMetadata->getConverts() as $convert) {
                // XXX: convert() only takes paths relative to web root, currently
                $this->imManager->convert($convert->params, $file->getPathName());
            }
        }
        return null;
    }
}
