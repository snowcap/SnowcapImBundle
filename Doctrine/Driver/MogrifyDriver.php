<?php

namespace Snowcap\ImBundle\Doctrine\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Snowcap\ImBundle\Doctrine\Metadata\MogrifyMetadata;

class MogrifyDriver implements DriverInterface
{
    const MOGRIFY = 'Snowcap\ImBundle\Doctrine\Mapping\Mogrify';

    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new MergeableClassMetadata($class->getName());

        foreach ($class->getProperties() as $property) {
            $field = $this->reader->getPropertyAnnotation($property, self::MOGRIFY);

            if(!is_null($field)) {
                $propertyMetadata = new MogrifyMetadata($class->getName(), $property->getName());
                $propertyMetadata->params = $field->params;

                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }
}
