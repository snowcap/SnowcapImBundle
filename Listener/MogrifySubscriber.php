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
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Snowcap\ImBundle\Manager as ImManager;

use Doctrine\ORM\Event\PreFlushEventArgs;

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
     * @var \Snowcap\ImBundle\Manager
     */
    private $imManager;

    /**
     * @param string    $rootDir   The dir to generate files
     * @param ImManager $imManager The ImBundle mamager instance
     */
    public function __construct($rootDir, ImManager $imManager)
    {
        $this->rootDir = $rootDir;
        $this->imManager = $imManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('loadClassMetadata', 'prePersist', 'preFlush');
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $meta = $eventArgs->getClassMetadata();
        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            /** @var $annotation \Snowcap\ImBundle\Doctrine\Mapping\Mogrify */
            if ($annotation = $reader->getPropertyAnnotation($property, 'Snowcap\\ImBundle\\Doctrine\\Mapping\\Mogrify')) {
                $field = $property->getName();
                $this->config[$meta->getTableName()]['fields'][$field] = array(
                    'property' => $property,
                    'params'   => $annotation->params,
                );
            }
        }
    }

    /**
     * @param PreFlushEventArgs $ea
     */
    public function preFlush(PreFlushEventArgs $ea)
    {
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $ea->getEntityManager();

        /** @var $unitOfWork \Doctrine\ORM\UnitOfWork */
        $unitOfWork = $entityManager->getUnitOfWork();

        $entityMaps = $unitOfWork->getIdentityMap();
        foreach ($entityMaps as $entities) {
            foreach ($entities as $entity) {
                foreach ($this->getFiles($entity, $ea->getEntityManager()) as $file) {
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
        foreach ($this->getFiles($entity, $ea->getEntityManager()) as $file) {
            $this->mogrify($entity, $file);
        }
    }

    private function getFiles($entity, \Doctrine\ORM\EntityManager $entityManager)
    {
        $classMetaData = $entityManager->getClassMetaData(get_class($entity));
        $tableName = $classMetaData->getTableName();

        if (array_key_exists($tableName, $this->config)) {
            return $this->config[$tableName]['fields'];
        } else {
            return array();
        }
    }

    private function mogrify($entity, $file)
    {
        $propertyName = $file['property']->name;

        $getter = 'get' . ucFirst($propertyName);
        if (method_exists($entity, $getter)) {
            /** @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
            $uploadedFile = $entity->$getter();
            if (null !== $uploadedFile) {
                $this->imManager->mogrify($file['params'], $uploadedFile->getPathName());
            }
        }
    }

    /**
     * @return string
     */
    private function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return $this->rootDir . '/../web/';
    }
}