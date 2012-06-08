<?php
namespace Snowcap\ImBundle\Doctrine\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation definition class
 *
 * @Annotation
 */
class Mogrify extends Annotation
{
    /** @var array */
    public $params;
}
