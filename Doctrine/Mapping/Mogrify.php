<?php
namespace Snowcap\ImBundle\Doctrine\Mapping;
use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 */
class Mogrify implements Annotation {
    /** @var array */
    public $params;
}
