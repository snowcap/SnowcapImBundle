<?php

namespace Snowcap\ImBundle\Doctrine\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Snowcap\ImBundle\Doctrine\Metadata\MogrifyMetadata;
use Snowcap\ImBundle\Doctrine\Metadata\ConvertMetadata;

class AnnotationDriver implements DriverInterface
{
    const MOGRIFY = 'Snowcap\ImBundle\Doctrine\Mapping\Mogrify';

    const CONVERT = 'Snowcap\ImBundle\Doctrine\Mapping\Convert';

    const CONVERT_MULTIPLE = 'Snowcap\ImBundle\Doctrine\Mapping\ConvertMultiple';

    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    private function getMogrifyMetadata(\ReflectionClass $class)
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

    private function getConvertMetadata(\ReflectionClass $class)
    {
        $classMetadata = new MergeableClassMetadata($class->getName());

        foreach ($class->getProperties() as $property) {
            $multiple = $this->reader->getPropertyAnnotation($property, self::CONVERT_MULTIPLE);
            if(!is_null($multiple)) {

                $propertyMetadata = new ConvertMetadata($class->getName(), $property->getName());
                foreach($multiple->value as $convert) {
                    $propertyMetadata->addConvert($convert->params, $convert->targetProperty);
                }

            } else {

                $convert = $this->reader->getPropertyAnnotation($property, self::CONVERT);
                if(!is_null($convert)) {
                    $propertyMetadata = new ConvertMetadata($class->getName(), $property->getName());
                    $propertyMetadata->addConvert($convert->params, $convert->targetProperty);
                }

            }

            if(isset($propertyMetadata)) {
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new MergeableClassMetadata($class->getName());
        $classMetadata->merge($this->getMogrifyMetadata($class));
        $classMetadata->merge($this->getConvertMetadata($class));

        return $classMetadata;
    }
}
