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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Snowcap\ImBundle\Manager as ImManager;

use Metadata\MetadataFactoryInterface;

/**
 * Event listener for Doctrine entities to evualuate and execute ImBundle annotations
 */
class MogrifySubscriber implements EventSubscriber
{
    private $config = array();

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var \Metadata\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Snowcap\ImBundle\Manager
     */
    private $imManager;

    /**
     * @param string                    $rootDir            The dir to generate files
     * @param MetadataFactoryInterface  $metadataFactory
     * @param ImManager                 $imManager          The ImBundle manager instance
     */
    public function __construct($rootDir, MetadataFactoryInterface $metadataFactory, ImManager $imManager)
    {
        $this->rootDir = $rootDir;
        $this->metadataFactory = $metadataFactory;
        $this->imManager = $imManager;
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
        $propertyName = $file->name;

        $getter = 'get' . ucFirst($propertyName);
        if (method_exists($entity, $getter)) {
            /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
            $uploadedFile = $entity->$getter();
            if (null !== $uploadedFile) {
                $this->imManager->mogrify($file->params, $uploadedFile->getPathName());
            }
        }
    }
}
