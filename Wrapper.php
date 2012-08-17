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

use Snowcap\ImBundle\Exception\InvalidArgumentException;
use Snowcap\ImBundle\Exception\RuntimeException;

/**
 * Im wrapper
 *
 * Imagemagick command line wrapper
 *
 * Used by the manager
 *
 */
class Wrapper
{
    private $processClass;

    private $binaryPath;

    private $_acceptedBinaries = array(
        'animate', 'compare', 'composite',
        'conjure', 'convert', 'display',
        'identify', 'import', 'mogrify',
        'montage', 'stream'
    );

    /**
     * @param string $processClass The class name of the command line processor
     * @param string $binaryPath   The path where the Imagemagick binaries lies
     */
    public function __construct($processClass, $binaryPath = "")
    {
        $this->binaryPath = empty($binaryPath) ? $binaryPath : rtrim($binaryPath, '/') . '/';
        $this->processClass = $processClass;
    }

    /**
     * Shortcut to construct & run an Imagemagick command
     *
     * @param string $command    @see _self::buildCommand
     * @param string $inputfile  @see _self::buildCommand
     * @param array  $attributes @see _self::buildCommand
     * @param string $outputfile @see _self::buildCommand
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function run($command, $inputfile, $attributes = array(), $outputfile = "")
    {
        $commandString = $this->buildCommand($command, $inputfile, $attributes, $outputfile);

        return $this->rawRun($commandString);
    }

    /**
     * @param string $command    Imagemagick command (convert, mogrify, ...)
     * @param string $inputfile  Source file to use
     * @param array  $attributes Array of Imagemagick key/values attributes
     * @param string $outputfile Destination file - used when converting
     *
     * @return string
     */
    private function buildCommand($command, $inputfile, $attributes = array(), $outputfile = "")
    {
        $attributesString = trim($this->prepareAttributes($attributes));
        if (strlen($attributesString) > 0) {
            $attributesString = " " . $attributesString;
        }

        if ($outputfile !== "") {
            $this->checkDirectory($outputfile);

            $commandString = $this->binaryPath . $command . " " . $inputfile . $attributesString . " " . $outputfile;
        } else {
            $commandString = $this->binaryPath . $command . $attributesString . " " . $inputfile;
        }

        $this->validateCommand($commandString);

        return $commandString;
    }

    /**
     * Run a command. Only use if the run() method doesn't fit your need
     *
     * @param string $commandString
     *
     * @return string
     * @throws RuntimeException
     */
    public function rawRun($commandString)
    {
        $this->validateCommand($commandString);

        /** @var $process \Symfony\Component\Process\Process */
        $process = new $this->processClass($commandString);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Takes an array of attributes and formats it as CLI parameters
     *
     * @param array $attributes
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function prepareAttributes($attributes = array())
    {
        if (!is_array($attributes)) {
            throw new InvalidArgumentException("[ImBundle] format attributes must be an array, recieved: " . var_export($attributes, true));
        }
        $result = "";
        foreach ($attributes as $key => $value) {
            if ($key === null || $key === "") {
                $result .= " " . $value;
            } else {
                $result .= " -" . $key;
                if ($value != "") {
                    $result .= " \"" . $value . "\"";
                }
            }
        }

        return $result;
    }

    /**
     * Creates the given directory if unexistant
     *
     * @param string $path
     *
     * @throws RuntimeException
     */
    public function checkDirectory($path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new RuntimeException(sprintf('Unable to create the "%s" directory', $dir));
            }
        }
    }

    /**
     * Validates that the command launches a Imagemagick command line tool executable
     *
     * @param string $commandString
     *
     * @throws InvalidArgumentException
     * @return boolean
     */
    private function validateCommand($commandString)
    {
        $cmdParts = explode(" ", $commandString);

        if (count($cmdParts) < 2) {
            throw new InvalidArgumentException("This command isn't properly structured : '" . $commandString . "'");
        }

        $binaryPath = $cmdParts[0];
        $binaryPathParts = explode('/', $binaryPath);
        $binary = $binaryPathParts[count($binaryPathParts)-1];

        if (!in_array($binary, $this->_acceptedBinaries)) {
            throw new InvalidArgumentException("This command isn't part of the ImageMagick command line tools : '" . $binary . "'");
        }

        return true;
    }
}
