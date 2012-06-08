<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new Twig_TokenParser_Imresize(),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'imresize' => new \Twig_Filter_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'imresize' => new \Twig_Function_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    /**
     * Called by the compile method for each <img> tag found
     *
     * @param string $imgTag
     *
     * @return string
     */
    public function convert($imgTag)
    {
        $crawler = new Crawler();
        $crawler->addContent($imgTag);
        $tag = $crawler->filter("img");

        try {
            $src = $tag->attr("src");
            $width = $tag->attr("width");
            $height = $tag->attr("height");

            if ($width == null && $height == null) {
                return $imgTag;
            }

            $format = $width . "x" . $height;

            return preg_replace("| src=[\"']" . $src . "[\"']|", " src=\"" . $this->imResize($src, $format) . "\"", $imgTag);
        } catch (\Exception $e) {
            return $imgTag;
        }
    }

    /**
     * Returns the cached path, after executing the asset twig function
     *
     * @param string $path   Path of the source file
     * @param string $format Imbundle format string
     *
     * @return mixed
     */
    public function imResize($path, $format)
    {
        if (strpos($path, "http://") === 0 || strpos($path, "https://") === 0) {
            $path = str_replace("://", "/", $path);
        }

        if (strpos($path, "/") === 0) {
            $separator = "";
        } else {
            $separator = "/";
        }

        return $this->container->get('templating.helper.assets')->getUrl("cache/im/" . $format . $separator . trim($path));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'snowcap_im';
    }
}
