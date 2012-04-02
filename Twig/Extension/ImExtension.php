<?php
namespace Snowcap\ImBundle\Twig\Extension;

use Snowcap\ImBundle\Twig\TokenParser\Imresize as Twig_TokenParser_Imresize;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Registering twig extensions
 */
class ImExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

    /**
     * Called by the compile method for each <img> tag found
     *
     * @param $imgTag array of matches
     * @return string
     */
    public function convert($imgTag)
    {
        $crawler = new Crawler();
        $crawler->addContent($imgTag);
        $tag = $crawler->filter("img");

        $src = $tag->attr("src");
        $width = $tag->attr("width");
        $height = $tag->attr("height");

        if($width == null && $height == null) {
            return $imgTag;
        }

        $format = $width . "x" . $height;

        return preg_replace("| src=[\"']" . $src . "[\"']|"," src=\"" . $this->imResize($src, $format) . "\"", $imgTag);
    }

    /**
     *
     * Returns the cached path, after executing the asset twig function
     *
     * @param $path string
     * @param $format format
     * @return mixed
     */
    public function imResize($path, $format)
    {
        if(strpos($path,"http://") === 0 || strpos($path,"https://") === 0) {
            $path = str_replace("://","/",$path);
        }

        if(strpos($path,"/") === 0) {
            $separator = "";
        } else {
            $separator = "/";
        }

        return $this->container->get('templating.helper.assets')->getUrl("cache/im/" . $format . $separator . trim($path));
    }

    public function getName()
    {
        return 'snowcap_im';
    }
}
