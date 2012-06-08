<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Command line task to clear (remove) generated files
 */
class ClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('snowcap:im:clear')
            ->setDescription('Clear IM cache')
            ->addArgument('age', InputArgument::OPTIONAL, 'Clear only files older than (days)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $kernel \Symfony\Component\HttpKernel\Kernel */
        $kernel = $this->getContainer()->get('kernel');
        $cacheDir = $kernel->getRootDir() . '/../web/cache/im/';

        /** @var $filesystem \Symfony\Component\Filesystem\Filesystem */
        $filesystem = $this->getContainer()->get('filesystem');

        $age = $input->getArgument('age');
        if ($age) {

            $output->writeln(sprintf('Clearing the IM cache older than %s days', $age));

            $finder = new Finder();
            foreach ($finder->in($cacheDir)->files()->date('until ' . $age . ' days ago') as $file) {
                $filesystem->remove($file);
            }

            // removing empty directories
            $process = new Process("find " . $cacheDir . " -type d -empty");
            $process->run();
            $emptyDirectories = explode("\n", $process->getOutput());
            foreach ($emptyDirectories as $directory) {
                if ($directory != "." && $directory != ".." && $directory != "") {
                    $filesystem->remove($directory);
                }
            }

        } else {

            $output->writeln('Clearing all the IM cache');

            $filesystem->remove($cacheDir);
        }
    }
}
