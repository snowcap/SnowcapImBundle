<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Tests\Mock;

use \Symfony\Component\Process\Process as BaseProcess;

/**
 * Mock object for the process class
 */
class Process extends BaseProcess
{
    private $cmd;
    private $success;

    /**
     * @param string $cmd
     * @param null $cwd
     * @param array $env
     * @param null $input
     * @param int $timeout
     * @param array $options
     */
    public function __construct($cmd, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        $this->cmd = $cmd;
    }

    /**
     * Run the process
     * @param null $callback
     * @return int|void
     */
    public function run($callback = null)
    {
        if ($this->cmd === 'mogrify "somefailingstructure') {
            $this->success = false;
        } else {
            $this->success = true;
        }
    }

    /**
     * @return mixed
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return "output";
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return "errormsg";
    }
}
