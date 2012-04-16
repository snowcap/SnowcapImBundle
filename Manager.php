<?php

namespace Snowcap\ImBundle;

use Symfony\Component\HttpKernel\Kernel;
use Snowcap\ImBundle\Wrapper;

/**
 * Im manager
 */
class Manager
{
    const DEFAULT_IM_PATH = 'cache/im/';

    /**
     * @var Wrapper
     */
    private $wrapper;


    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $formats;

    private $web_path;

    private $im_path;

    private $cache_path;

    public function __construct(Wrapper $wrapper, Kernel $kernel, $formats = array())
    {
        $this->wrapper = $wrapper;
        $this->kernel = $kernel;
        $this->formats = $formats;
        $this->web_path = $this->kernel->getRootDir() . "/../web/";
        $this->im_path = self::DEFAULT_IM_PATH;
        $this->cache_path = $this->web_path . $this->im_path;
    }

    public function setCachePath($path)
    {
        $this->im_path = $path;
        $this->cache_path = $this->web_path . $this->im_path;
    }

    /**
     * To know if a cache exist for a image in a format
     *
     * @param string $format
     * @param string $path
     * @return bool
     */
    public function cacheExists($format, $path)
    {
         return (file_exists($this->cache_path . $format . '/' . $path) === true);
    }

    /**
     * To get a cached image content
     *
     * @param string $format
     * @param string $path
     * @return string
     */
    public function getCacheContent($format, $path)
    {
        return file_get_contents($this->cache_path . $format . '/' . $path);
    }

    /**
     * To get the web path for a format
     *
     * @param $format
     * @param $path
     * @return string
     */
    public function getUrl($format, $path)
    {
        return $this->im_path . $format . '/' . $path;
    }

    /**
     * Shortcut to run a "convert" command => creates a new image
     *
     * @param $format
     * @param $inputfile
     * @return string
     */
    public function convert($format, $inputfile)
    {
        $this->checkImage($inputfile);
        return $this->wrapper->run("convert", $this->web_path . $inputfile, $this->convertFormat($format), $this->cache_path . $format . '/' . $inputfile);
    }

    /**
     * Shortcut to run a "mogrify" command => modifies the image source
     *
     * @param string $format
     * @param string $inputfile
     * @return string
     */
    public function mogrify($format, $file)
    {
        $this->checkImage($file);
        return $this->wrapper->run("mogrify", $file, $this->convertFormat($format));
    }

    /**
     * Returns the attributes for converting the image regarding a specific format
     *
     * @param string $format
     * @return array
     * @throws \Exception
     */
    private function convertFormat($format)
    {
        if(is_array($format)) {
            // sounds like the format is already done, let's keep it as it is
            return $format;
        }
        if(array_key_exists($format,$this->formats)) {
            // it's a format defined in config, let's use all defined parameters
            return $this->formats[$format];
        } elseif(preg_match("/^([0-9]*)x([0-9]*)/",$format)) {
            // it's a custom [width]x[height] format, let's make a thumb
            return array('thumbnail' => $format);
        } else {
            throw new \Exception(sprintf("Unknown IM format: %s", $format));
        }
    }

    /**
     * Validates that an image exists
     *
     * @param string $path
     * @throws \Exception
     */
    private function checkImage($path)
    {
        if(!file_exists($this->web_path . $path) && !file_exists($path)) {
            throw new \Exception(sprintf("Unable to find the image \"%s\" to cache",$path));
        }
    }

}
