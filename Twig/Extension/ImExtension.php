<?php
namespace Snowcap\ImBundle\Twig\Extension;

use Snowcap\ImBundle\Twig\TokenParser\Imresize as Twig_TokenParser_Imresize;

/**
 * Registering twig extensions
 */
class ImExtension extends \Twig_Extension
{
    public function getTokenParsers()
    {
        return array(
            new Twig_TokenParser_Imresize(),
        );
    }

    public function getName()
    {
        return 'snowcap_im';
    }
}
