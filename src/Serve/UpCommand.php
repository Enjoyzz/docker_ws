<?php

namespace Enjoys\DockerWs\Serve;

use Enjoys\DockerWs\Variables;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'start')]
class UpCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process([
            'docker-compose',
            '--file',
            Variables::$rootPath . '/docker-compose.yml',
            '--env-file',
            Variables::$rootPath . '/.docker.env',
            'up',
            '--build',
            '--remove-orphans',
            '-d'
        ]);

        $process->setTimeout(null)
            ->setTty(true)
            ->run()
        ;

        $output->writeln($process->getOutput(), OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln($process->getErrorOutput(), OutputInterface::VERBOSITY_VERY_VERBOSE);

        $output->writeln('Run docker container');

        return Command::SUCCESS;
    }

}