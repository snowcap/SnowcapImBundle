<?php

namespace Snowcap\ImBundle\Doctrine\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class ConvertMetadata extends BasePropertyMetadata
{
    /**
     * @var array
     */
    private $converts;

    public function addConvert($params, $targetProperty)
    {
        $convert = new \stdClass;
        $convert->params = $params;
        $convert->targetProperty = $targetProperty;
        $this->converts[] = $convert;
    }

    public function getConverts()
    {
        return $this->converts;
    }

    public function serialize()
    {
        return serialize(array(
            $this->class,
            $this->name,
            $this->converts,
        ));
    }

    public function unserialize($str)
    {
        list($this->class, $this->name, $this->converts) = unserialize($str);

        $this->reflection = new \ReflectionProperty($this->class, $this->name);
        $this->reflection->setAccessible(true);
    }
}
