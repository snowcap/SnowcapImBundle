<?php

namespace Snowcap\ImBundle\Doctrine\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class MogrifyMetadata extends BasePropertyMetadata
{
    /** @var string|array<string> */
    public $params;
}
