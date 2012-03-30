<?php

namespace Snowcap\ImBundle\Twig\Node;

use Twig_Node;
use Twig_NodeInterface;
use Twig_Compiler;

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
            ->write("echo \$this->env->getExtension('snowcap_im')->convert(ob_get_clean());\n")
        ;
    }


}
