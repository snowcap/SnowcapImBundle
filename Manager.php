<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Kernel;
use Snowcap\ImBundle\Wrapper;

use Snowcap\ImBundle\Exception\NotFoundException;
use Snowcap\ImBundle\Exception\InvalidArgumentException;

/**
 * Im manager
 */
class Manager
{
    const DEFAULT_IM_PATH = 'cache/im/';

    /**
     * @var Wrapper
     */
    protected $wrapper;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $kernel;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $webPath;

    /**
     * @var string
     */
    protected $imPath;

    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @param Wrapper $wrapper The ImBundle Wrapper instance
     * @param Kernel  $kernel  Symfony Kernel component instance
     * @param array   $formats Formats definition
     */
    public function __construct(Wrapper $wrapper, Kernel $kernel, $formats = array())
    {
        $this->wrapper = $wrapper;
        $this->kernel = $kernel;
        $this->formats = $formats;
        $this->webPath = $this->kernel->getRootDir() . "/../web/";
        $this->imPath = self::DEFAULT_IM_PATH;
        $this->cachePath = $this->webPath . $this->imPath;
    }

    /**
     * Add a format to the config
     *
     * @param string $name
     * @param string $config
     */
    public function addFormat($name, $config)
    {
        $this->formats[$name] = $config;
    }

    /**
     * @param string $path
     */
    public function setCachePath($path)
    {
        $this->imPath = $path;
        $this->cachePath = $this->webPath . $this->imPath;
    }

    /**
     * To know if a cache exist for a image in a format
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return bool
     */
    public function cacheExists($format, $path)
    {
        return (file_exists($this->cachePath . $format . '/' . $path) === true);
    }

    /**
     * To get a cached image content
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return string
     */
    public function getCacheContent($format, $path)
    {
        return file_get_contents($this->cachePath . $format . '/' . $path);
    }

    /**
     * To get the web path for a format
     *
     * @param string $format ImBundle format string
     * @param string $path   Source file path
     *
     * @return string
     */
    public function getUrl($format, $path)
    {
        return $this->imPath . $format . '/' . $path;
    }

    /**
     * Shortcut to run a "convert" command => creates a new image
     *
     * @param string $format    ImBundle format string
     * @param string $inputfile Source file path
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function convert($format, $inputfile)
    {
        $this->checkImage($inputfile);

        return $this->wrapper->run("convert", $this->webPath . $inputfile, $this->convertFormat($format), $this->cachePath . $this->pathify($format) . '/' . $inputfile);
    }

    /**
     * Shortcut to run a "mogrify" command => modifies the image source
     *
     * @param string $format ImBundle format string
     * @param string $file   Source file path
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function mogrify($format, $file)
    {
        $this->checkImage($file);

        return $this->wrapper->run("mogrify", $file, $this->convertFormat($format));
    }

    /**
     * @param string $format ImBundle format string
     * @param string $path   cached path for an external image - ex: http/somepath/somefile.jpg or https/somepath/someotherfile.jpg
     *
     * The cached path is equivalent to the original path except that the '://' syntax after the protocol is replaced by a simple "/", to conserve a correct URL encoded string
     * The Twig tag 'imResize' will automatically make this conversion for you
     *
     * @return string
     */
    public function downloadExternalImage($format, $path)
    {
        $protocol = substr($path, 0, strpos($path, "/"));
        $newPath = str_replace($protocol . "/", $this->kernel->getRootDir() . '/../web/cache/im/' . $format . '/' . $protocol . '/', $path);

        $this->wrapper->checkDirectory($newPath);

        $fp = fopen($newPath, 'w');

        $ch = curl_init(str_replace($protocol . '/', $protocol . '://', $path));
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $newPath;
    }

    /**
     * Returns the attributes for converting the image regarding a specific format
     *
     * @param string $format
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function convertFormat($format)
    {
        if (is_array($format)) {
            // sounds like the format is already done, let's keep it as it is
            return $format;
        }
        if (array_key_exists($format, $this->formats)) {
            // it's a format defined in config, let's use all defined parameters
            return $this->formats[$format];
        } elseif (preg_match("/^([0-9]*)x([0-9]*)/", $format)) {
            // it's a custom [width]x[height] format, let's make a thumb
            return array('thumbnail' => $format);
        } else {
            throw new InvalidArgumentException(sprintf("Unknown IM format: %s", $format));
        }
    }

    /**
     * Validates that an image exists
     *
     * @param string $path
     *
     * @throws NotFoundException
     * @throws HttpException
     */
    private function checkImage($path)
    {
        if (!file_exists($this->webPath . $path) && !file_exists($path)) {
            throw new NotFoundException(sprintf("Unable to find the image \"%s\" to cache", $path));
        }

        if(!is_file($this->webPath . $path) && !is_file($path)) {
            throw new HttpException(400, sprintf('[ImBundle] "%s" is no file', $path));
        }
    }

    /**
     * Takes a format (array or string) and return it as a valid path string
     *
     * @param mixed $format
     *
     * @return string
     */
    private function pathify($format)
    {
        if (is_array($format)) {
            return md5(serialize($format));
        } else {
            return $format;
        }
    }
}
