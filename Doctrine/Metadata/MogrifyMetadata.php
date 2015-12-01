<?php

namespace Snowcap\ImBundle\Doctrine\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class MogrifyMetadata extends BasePropertyMetadata
{
    /**
     * @var string|array<string>
     */
    public $params;

    public function serialize()
    {
        return serialize(array(
            $this->class,
            $this->name,
            $this->params,
        ));
    }

    public function unserialize($str)
    {
        list($this->class, $this->name, $this->params) = unserialize($str);

        $this->reflection = new \ReflectionProperty($this->class, $this->name);
        $this->reflection->setAccessible(true);
    }
}
