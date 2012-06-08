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

/**
 * Mock object for the process class
 */
class Process
{
    private $cmd;
    private $success;

    /**
     * @param string $cmd
     */
    public function __construct($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * Run the process
     */
    public function run()
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
