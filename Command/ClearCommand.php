<?php
namespace Snowcap\ImBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class ClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('snowcap:im:clear')
            ->setDescription('Clear IM cache')
            ->addArgument('age', InputArgument::OPTIONAL, 'Clear only files older than (days)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache_dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/cache/im/';
        $filesystem = $this->getContainer()->get('filesystem');

        $age = $input->getArgument('age');
        if ($age) {

            $output->writeln(sprintf('Clearing the IM cache older than %s days', $age));

            $finder = new Finder();
            foreach($finder->in($cache_dir)->files()->date('until ' . $age . ' days ago') as $file) {
                $filesystem->remove($file);
            }

            // removing empty directories
            $process = new Process("find " . $cache_dir . " -type d -empty");
            $process->run();
            $emptyDirectories = explode("\n",$process->getOutput());
            foreach($emptyDirectories as $directory) {
                if($directory != "." && $directory != ".." && $directory != "") {
                    $filesystem->remove($directory);
                }
            }

        } else {

            $output->writeln('Clearing all the IM cache');

            $filesystem->remove($cache_dir);
        }
    }
}