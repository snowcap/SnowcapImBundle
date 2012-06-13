<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Twig\TokenParser;

use \Twig_TokenParser;
use \Twig_Token;
use \Twig_NodeInterface;

use \Snowcap\ImBundle\Twig\Node\Imresize as Twig_Node_Imresize;

/**
 * Create and use image cache regarding the HTML width and height attributes
 *
 * <pre>
 * {% imresize %}
 *      <div>
 *          <img src="/some/img.jpg" width="100" />
 *          <img src="{{ asset('some/img.jpg'}}" height="100" />
 *      </div>
 * {% endimresize %}
 * </pre>
 *
 * @codeCoverageIgnore
 *
 */
class Imresize extends Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideImresizeEnd'), true);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new Twig_Node_Imresize($body, $lineno, $this->getTag());
    }

    /**
     * @param Twig_Token $token
     *
     * @return bool
     */
    public function decideImresizeEnd(Twig_Token $token)
    {
        return $token->test('endimresize');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'imresize';
    }
}
