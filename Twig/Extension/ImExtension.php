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
     *
     * @codeCoverageIgnore
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getTokenParsers()
    {
        return array(
            new Twig_TokenParser_Imresize(),
        );
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getFilters()
    {
        return array(
            'imresize' => new \Twig_Filter_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        return array(
            'imresize' => new \Twig_Function_Method($this, 'imResize', array('pre_escape' => 'html', 'is_safe' => array('html'))),
        );
    }

    /**
     * Called by the compile method to replace the image sources with image cache sources
     *
     * @param string $html
     *
     * @return string
     */
    public function convert($html)
    {
        preg_match_all('|<img ([^>]+)>|', $html, $matches);

        foreach($matches[0] as $img)
        {
            $crawler = new Crawler();
            $crawler->addContent($img);
            $imgTag = $crawler->filter("img");

            $src = $imgTag->attr('src');
            $width = $imgTag->attr('width');
            $height = $imgTag->attr('height');

            if (!empty($width) || !empty($height)) {
                $format = $width . "x" . $height;
                $updatedTagString = preg_replace("| src=[\"']" . $src . "[\"']|", " src=\"" . $this->imResize($src, $format) . "\"", $img);
                $html = str_replace($img, $updatedTagString, $html);
            }
        }

        return $html;
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
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'snowcap_im';
    }
}
