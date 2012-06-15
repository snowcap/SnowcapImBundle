<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

use Snowcap\ImBundle\Wrapper;

/**
 * Wrapper tester class
 */
class WrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Wrapper */
    private $wrapper;

    /**
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Pre tasks
     */
    public function setUp()
    {
        $this->wrapper = new Wrapper('\Snowcap\ImBundle\Tests\Mock\Process');
        $this->root = vfsStream::setup('exampleDir');
    }

    /**
     * @param array  $attributes Some attributes to send
     * @param string $expected   The string we expect as return
     *
     * @dataProvider providerPrepareAttributes
     */
    public function testPrepareAttributes($attributes, $expected)
    {
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $attributes));
    }

    /**
     * @return array
     */
    public function providerPrepareAttributes()
    {
        return array(
            array(
                array(),
                '',
            ),
            array(
                array(
                    'resize' => '150x150^',
                ),
                ' -resize "150x150^"',
            ),
            array(
                array(
                    'resize' => '120x',
                    null     => '+opaque -transparent'
                ),
                ' -resize "120x" +opaque -transparent',
            ),
        );
    }

    /**
     * @param array $attributes
     *
     * @expectedException InvalidArgumentException
     *
     * @dataProvider providerPrepareAttributesException
     */
    public function testPrepareAttributesException($attributes)
    {
        $method = new \ReflectionMethod($this->wrapper, 'prepareAttributes');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $attributes);
    }

    /**
     * @return array
     */
    public function providerPrepareAttributesException()
    {
        return array(
            array('some crappy string'),
            array(new \stdClass()),
        );
    }

    /**
     * @param string $command    @see Wrapper::buildCommand
     * @param string $inputfile  @see Wrapper::buildCommand
     * @param array  $attributes @see Wrapper::buildCommand
     * @param string $outputfile @see Wrapper::buildCommand
     * @param string $expected   The string we expect as return
     *
     * @dataProvider providerBuildCommand
     */
    public function testBuildCommand($command, $inputfile, $attributes, $outputfile, $expected)
    {
        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->wrapper, $command, $inputfile, $attributes, $outputfile));
    }

    /**
     * @return array
     */
    public function providerBuildCommand()
    {
        return array(
            array('convert', 'somefile', array(), 'anotherfile', 'convert somefile anotherfile'),
            array('mogrify', 'somefile', array('resize' => '450x'), '', 'mogrify -resize "450x" somefile'),
            array('montage', 'somefile', array('resize' => '450x'), '', 'montage -resize "450x" somefile'),
        );
    }

    /**
     * @param string $command    @see Wrapper::buildCommand
     * @param string $inputfile  @see Wrapper::buildCommand
     * @param array  $attributes @see Wrapper::buildCommand
     * @param string $outputfile @see Wrapper::buildCommand
     *
     * @expectedException InvalidArgumentException
     *
     * @dataProvider providerBuildCommandException
     */
    public function testBuildCommandException($command, $inputfile, $attributes, $outputfile)
    {
        $method = new \ReflectionMethod($this->wrapper, 'buildCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $command, $inputfile, $attributes, $outputfile);
    }

    /**
     * @return array
     */
    public function providerBuildCommandException()
    {
        return array(
            array('ls', 'somefile', array(), 'anotherfile'),
            array('blaarhh', '', array(), ''),
        );
    }

    /**
     * Testing the rawRun method
     */
    public function testRawRun()
    {
        $this->assertEquals('output', $this->wrapper->rawRun('mogrify -resize 120x somefile'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRawRunInvalidException()
    {
        $this->wrapper->rawRun('crap');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRawRunRuntimeException()
    {
        $this->wrapper->rawRun('mogrify "somefailingstructure');
    }

    /**
     * @param string $commandString @see Wrapper::validateCommand
     *
     * @dataProvider providerValidateCommand
     */
    public function testValidateCommand($commandString)
    {
        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->wrapper, $commandString));
    }

    /**
     * @return array
     */
    public function providerValidateCommand()
    {
        return array(
            array('convert somestrings'),
            array('mogrify somestrings blouh +yop -paf -bim "zoup"'),
        );
    }

    /**
     * @param string $commandString
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider providerValidateCommandException
     */
    public function testValidateCommandException($commandString)
    {
        $method = new \ReflectionMethod($this->wrapper, 'validateCommand');
        $method->setAccessible(true);

        $method->invoke($this->wrapper, $commandString);
    }

    /**
     * @return array
     */
    public function providerValidateCommandException()
    {
        return array(
            array('convert'),
            array('bignou'),
            array('bignou didjou'),
        );
    }

    /**
     * Checking folder creation & retrieval
     */
    public function testCheckDirectory()
    {
        $this->assertFalse($this->root->hasChild('mypath'));
        $this->wrapper->checkDirectory(vfsStream::url('exampleDir/mypath/.'));
        $this->assertTrue($this->root->hasChild('mypath'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCheckDirectoryException()
    {
        $method = new \ReflectionMethod($this->wrapper, 'checkDirectory');
        $method->setAccessible(true);

        vfsStreamWrapper::getRoot()->chmod(0400);
        $method->invoke($this->wrapper, vfsStream::url('exampleDir/mypath/.'));
    }
}
