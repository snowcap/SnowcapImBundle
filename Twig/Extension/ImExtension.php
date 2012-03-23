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

    public function getFilters()
    {
        return array(
            'imresize' => new \Twig_Filter_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    public function getFunctions()
    {
        return array(
            'imresize' => new \Twig_Function_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    public function imResize($path, $format)
    {
        return "/cache/im/" . $format . "/" . trim($path);
    }

    public function getName()
    {
        return 'snowcap_im';
    }
}
