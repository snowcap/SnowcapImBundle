<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Tests\Twig\Extension;

use Snowcap\ImBundle\Twig\Extension\ImExtension;

/**
 * Wrapper tester class
 */
class ImExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImExtension */
    private $imExtension;

    public function setUp()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('get')->with($this->equalTo('templating.helper.assets'))->will($this->returnValue(new \Symfony\Component\Templating\Helper\AssetsHelper()));
        $this->imExtension = new ImExtension($container);
    }

    /**
     * @param string $input    the string to parse
     * @param string $expected what we except as parsing in return
     *
     * @dataProvider providerConvert
     */
    public function testConvert($input, $expected)
    {
        $this->assertEquals($expected, $this->imExtension->convert($input));
    }

    /**
     * @return array
     */
    public function providerConvert()
    {
        return array(
            array('hop hop', 'hop hop'),
            array('<img src="img.jpg"/>', '<img src="img.jpg"/>'),
            array('hop <img src="img.jpg" />hop', 'hop <img src="img.jpg" />hop'),
            array('hop <img src="img.jpg" width=""/>hop', 'hop <img src="img.jpg" width=""/>hop'),
            array('hop <img src="img.jpg" width="100" />hop', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop'),
            array('hop <img src="path/img.jpg" height="120"/>hop', 'hop <img src="/cache/im/x120/path/img.jpg" height="120"/>hop'),
            array('hop <img src="path/img.jpg" width="100" height="120" />hop', 'hop <img src="/cache/im/100x120/path/img.jpg" width="100" height="120" />hop'),
            array('hop <img height="100" src="path/img.jpg"  width="120" data="content" />hop', 'hop <img height="100" src="/cache/im/120x100/path/img.jpg"  width="120" data="content" />hop'),
            array('hop <img height="100" src="path/img.jpg"  width="120" data="path/img.jpg" />hop', 'hop <img height="100" src="/cache/im/120x100/path/img.jpg"  width="120" data="path/img.jpg" />hop'),
            array('hop <img src="img.jpg" width="100" />hop <img src="img2.jpg" width="100" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/100x/img2.jpg" width="100" /> hip'),
            array('hop <img src="img.jpg" width="100" />hop <img src="img.jpg" width="120" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/120x/img.jpg" width="120" /> hip'),
            array('hop <img src="img.jpg" width="100" />hop <img src="img.jpg" width="100" /> hip', 'hop <img src="/cache/im/100x/img.jpg" width="100" />hop <img src="/cache/im/100x/img.jpg" width="100" /> hip'),
        );
    }

    /**
     * @param string $path     File path
     * @param string $format   ImBundle format string
     * @param string $expected what we excpect as new url
     *
     * @dataProvider providerImResize
     */
    public function testImResize($path, $format, $expected)
    {
        $this->assertEquals($expected, $this->imExtension->imResize($path, $format));
    }

    /**
     * @return array
     */
    public function providerImResize()
    {
        return array(
            array('img.jpg', '100x', '/cache/im/100x/img.jpg'),
            array('img.png', '100x', '/cache/im/100x/img.png'),
            array('img.gif', '100x', '/cache/im/100x/img.gif'),
            array('img.tiff', 'x100', '/cache/im/x100/img.tiff'),
            array('/img.jpg', 'x100', '/cache/im/x100/img.jpg'),
            array('path/img.jpg', 'x100', '/cache/im/x100/path/img.jpg'),
            array('path/img.jpg', '120x100', '/cache/im/120x100/path/img.jpg'),
            array('http://domain.tld/path/img.jpg', '120x100', '/cache/im/120x100/http/domain.tld/path/img.jpg'),
            array('https://domain.tld/path/img.jpg', '120x100', '/cache/im/120x100/https/domain.tld/path/img.jpg'),
        );
    }
}
