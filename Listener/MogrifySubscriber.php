<?php

namespace Snowcap\ImBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Snowcap\ImBundle\Manager as ImManager;

use Snowcap\CoreBundle\Doctrine\ORM\Event\PreFlushEventArgs;

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
     * @param string $rootDir
     */
    public function __construct($rootDir,ImManager $imManager){
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
        return array('loadClassMetadata','prePersist','preFlush');
    }

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
            if ($annotation = $reader->getPropertyAnnotation($property, 'Snowcap\\ImBundle\\Doctrine\\Mapping\\Mogrify')) {
                $field = $property->getName();
                $this->config[$meta->getTableName()]['fields'][$field] = array(
                    'property' => $property,
                    'params' => $annotation->params,
                );
            }
        }
    }

    public function preFlush(PreFlushEventArgs $ea)
    {
        /** @var $unitOfWork \Doctrine\ORM\UnitOfWork */
        $unitOfWork = $ea->getEntityManager()->getUnitOfWork();

        $entityMaps = $unitOfWork->getIdentityMap();
        foreach($entityMaps as $entities) {
            foreach($entities as $entity) {
                foreach($this->getFiles($entity,$ea->getEntityManager()) as $file) {
                    $this->mogrify($entity,$file);
                }
            }
        }
    }

    public function prePersist(LifecycleEventArgs $ea)
    {
        $entity = $ea->getEntity();
        foreach($this->getFiles($entity,$ea->getEntityManager()) as $file) {
            $this->mogrify($entity,$file);
        }
    }

    private function getFiles($entity, \Doctrine\ORM\EntityManager $entityManager)
    {
        $classMetaData = $entityManager->getClassMetaData(get_class($entity));
        $tableName = $classMetaData->getTableName();

        if(array_key_exists($tableName, $this->config)) {
            return $this->config[$tableName]['fields'];
        } else {
            return array();
        }
    }

    private function mogrify($entity, $file)
    {
        $propertyName = $file['property']->name;
        $uploadedFile = $entity->$propertyName;
        if (null !== $uploadedFile) {
            $this->imManager->mogrify($file['params'], $uploadedFile->getPathName());
        }
    }

    private function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return $this->rootDir . '/../web/';
    }
}