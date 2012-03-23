<?php

namespace Snowcap\ImBundle;

use Symfony\Component\HttpKernel\Kernel;
use Snowcap\ImBundle\Wrapper;

/**
 * Im manager
 */
class Manager
{

    /**
     * @var Wrapper
     */
    private $wrapper;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    public function __construct(Wrapper $wrapper, Kernel $kernel, $formats = array())
    {
        $this->wrapper = $wrapper;
        $this->kernel = $kernel;
        $this->formats = $formats;
        $this->web_path = $this->kernel->getRootDir() . "/../web/";
        $this->cache_path = $this->web_path . "cache/im/";
    }

    /**
     * To know if a cache exist for a image in a format
     *
     * @param $format
     * @param $path
     * @return bool
     */
    public function cacheExists($format, $path)
    {
         return (file_exists($this->cache_path . $format . '/' . $path) === true);
    }

    /**
     * To get a cached image content
     *
     * @param $format
     * @param $path
     * @return string
     */
    public function getCacheContent($format, $path)
    {
        return file_get_contents($this->cache_path . $format . '/' . $path);
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
     * @param $format
     * @param $inputfile
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
     * @param $format
     * @return array
     * @throws \Exception
     */
    private function convertFormat($format)
    {
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
     * @param $path
     * @throws \Exception
     */
    private function checkImage($path)
    {
        if(!file_exists($this->web_path . $path)) {
            throw new \Exception(sprintf("Unable to find the image \"%s\" to cache",$path));
        }
    }

}
