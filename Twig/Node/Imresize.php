<?php

namespace Snowcap\ImBundle\Twig\Node;

use Twig_Node;
use Twig_NodeInterface;
use Twig_Compiler;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Represents a img tag node
 *
 * It looks the HTML width and height attributes, and modifies the src attribute to load a cached image
 * with the proper size
 *
 */
class Imresize extends Twig_Node
{
    public function __construct(Twig_NodeInterface $body, $lineno, $tag = 'imresize')
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo preg_replace_callback('|<img[^>]+>|', '\\Snowcap\\ImBundle\\Twig\\Node\\Imresize::convert', ob_get_clean());\n")
        ;
    }

    /**
     * Called by the compile method for each <img> tag found
     *
     * @static
     * @param $imgTag array of matches
     * @return string
     */
    public static function convert($imgTag)
    {
        $crawler = new Crawler();
        $crawler->addContent($imgTag[0]);
        $tag = $crawler->filter("img");

        $src = $tag->attr("src");
        $width = $tag->attr("width");
        $height = $tag->attr("height");

        $format = $width . "x" . $height;

        return str_replace($src,"/cache/im/" . $format . "/" . trim($src), $imgTag[0]);
    }
}
