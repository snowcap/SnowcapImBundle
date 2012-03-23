<?php

namespace Snowcap\ImBundle;

use Symfony\Component\Process\Process;

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
    private $binary_path;

    public function __construct($binary_path = "")
    {
        $this->binary_path = $binary_path;
    }

    public function run($command, $inputfile, $attributes = array(), $outputfile = "")
    {
        $this->checkDirectory($outputfile);

        //echo $this->binary_path . $command . " " . $inputfile . " " . $this->prepareAttributes($attributes) . " " . $outputfile; die;

        $process = new Process($this->binary_path . $command . " " . $inputfile . " " . $this->prepareAttributes($attributes) . " " . $outputfile);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    private function prepareAttributes($attributes = array())
    {
        $result = "";
        foreach($attributes as $key => $value) {
            $result .= " -" . $key;
            if($value != "") {
                $result .= " \"" . $value . "\"";
            }
        }
        return $result;
    }

    private function checkDirectory($path) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \Exception(sprintf('Unable to create the "%s" directory', $dir));
            }
        }
    }
}
