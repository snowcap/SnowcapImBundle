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

use Snowcap\ImBundle\Manager;
use Snowcap\ImBundle\Wrapper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

/**
 * Manager tester class
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Initializing the vfsStream stream wrapper
     */
    public function setUp()
    {
        $this->rootDir = "vfs://app";
        $this->root = vfsStream::setup("/root");
    }

    /**
     * @return \Snowcap\ImBundle\Manager
     */
    public function test__construct()
    {
        $formats = array(
            'list' => array('resize' => '100x100')
        );

        $kernel = $this->getMock('Symfony\Component\HttpKernel\Kernel', array(), array('dev', 0));
        $kernel->expects($this->any())->method('getRootDir')->will($this->returnValue($this->rootDir));


        $wrapper = new Wrapper('\Snowcap\ImBundle\Tests\Mock\Process');
        $manager = new Manager($wrapper, $kernel, $formats);

        $this->assertEquals($wrapper, $this->getManagerPrivateValue('wrapper', $manager));
        $this->assertEquals($kernel, $this->getManagerPrivateValue('kernel', $manager));
        $this->assertEquals($formats, $this->getManagerPrivateValue('formats', $manager));
        $this->assertEquals($this->rootDir . '/../web/', $this->getManagerPrivateValue('webPath', $manager));
        $this->assertEquals(Manager::DEFAULT_IM_PATH, $this->getManagerPrivateValue('imPath', $manager));
        $this->assertEquals($this->rootDir . '/../web/' . Manager::DEFAULT_IM_PATH, $this->getManagerPrivateValue('cachePath', $manager));

        return $manager;
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testSetCachePath(Manager $manager)
    {
        $manager->setCachePath('somepath/');

        $this->assertEquals('somepath/', $this->getManagerPrivateValue('imPath', $manager));
        $this->assertEquals($this->rootDir . '/../web/somepath/', $this->getManagerPrivateValue('cachePath', $manager));

        $manager->setCachePath(Manager::DEFAULT_IM_PATH);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testCacheExists(Manager $manager)
    {
        $this->root = vfsStream::setup("/root");
        $filepath = "somefile";
        $format = "50x";
        $this->assertFalse($manager->cacheExists($format, $filepath));

        $structure = array(
            "app" => array(),
            "web" => array(
                "cache" => array(
                    "im" => array(
                        $format => array($filepath => 'somecontent')
                    )
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertTrue($manager->cacheExists($format, $filepath));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testGetCacheContent(Manager $manager)
    {
        $structure = array(
            "app" => array(),
            "web" => array(
                "cache" => array(
                    "im" => array(
                        'format' => array('somefile' => 'somecontent')
                    )
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $this->assertEquals('somecontent', $manager->getCacheContent('format', 'somefile'));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testGetUrl(Manager $manager)
    {
        $format = 'someformat';
        $path = 'somepath';

        $this->assertEquals(Manager::DEFAULT_IM_PATH . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath('somepath/');
        $this->assertEquals('somepath/' . $format . '/' . $path, $manager->getUrl($format, $path));
        $manager->setCachePath(Manager::DEFAULT_IM_PATH);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testConvertFormat(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $this->assertEquals(array('resize' => '100x100'), $method->invoke($manager, 'list'));
        $this->assertEquals(array('resize' => '100x100', 'crop' => '50x50+1+1'), $method->invoke($manager, array('resize' => '100x100', 'crop' => '50x50+1+1')));
        $this->assertEquals(array('thumbnail' => '100x100'), $method->invoke($manager, '100x100'));
        $this->assertEquals(array('thumbnail' => '100x'), $method->invoke($manager, '100x'));
        $this->assertEquals(array('thumbnail' => 'x100'), $method->invoke($manager, 'x100'));
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     * @expectedException InvalidArgumentException
     */
    public function testConvertFormatException(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'convertFormat');
        $method->setAccessible(true);

        $method->invoke($manager, 'someunknownformat');
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testCheckImage(Manager $manager)
    {
        $structure = array(
            "app" => array(),
            "web" => array(
                "uploads" => array(
                    'somefile' => 'somecontent'
                )
            )
        );
        $structureStream = vfsStream::create($structure);
        $this->root->addChild($structureStream);

        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'uploads/somefile');
        $method->invoke($manager, 'vfs://web/uploads/somefile');
        $this->assertTrue(true);
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     * @expectedException Snowcap\ImBundle\Exception\NotFoundException
     */
    public function testCheckImageException(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'checkImage');
        $method->setAccessible(true);

        $method->invoke($manager, 'someinexistantfile');
    }

    /**
     * @param Manager $manager
     *
     * @depends test__construct
     */
    public function testPathify(Manager $manager)
    {
        $method = new \ReflectionMethod($manager, 'pathify');
        $method->setAccessible(true);

        $simplePath = $method->invoke($manager, '200x150');
        $this->assertTrue(is_string($simplePath));

        $path = $method->invoke($manager, array('crop' => '100x100'));
        $this->assertTrue(is_string($path));

        $otherPath = $method->invoke($manager, array('crop' => '100x100+10'));
        $this->assertTrue(is_string($otherPath));

        $this->assertNotEquals($simplePath, $path);
        $this->assertNotEquals($path, $otherPath);
    }

    /**
     * @param Manager $manager
     *
     * @return \ReflectionClass
     */
    private function getManagerReflection(Manager $manager)
    {
        return new \ReflectionClass($manager);
    }

    /**
     * @param string  $propertyName The name of the private property
     * @param Manager $manager      The manager instance
     *
     * @return mixed
     */
    private function getManagerPrivateValue($propertyName, Manager $manager)
    {
        $reflection = $this->getManagerReflection($manager);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($manager);
    }
}
