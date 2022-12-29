<?php

namespace Enjoys\DockerWs\Serve;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'serve:up',
    description: '',
    aliases: ['up', 'start']
)]
class UpCommand extends Serve
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $process = new Process([
            'docker-compose',
            '--file',
            (getenv('ROOT_PATH') ?: '.') . '/docker-compose.yml',
            '--env-file',
            (getenv('ROOT_PATH') ?: '.') . '/.docker.env',
            'up',
            '--build',
            '--remove-orphans',
            '-d'
        ]);

        $process->setTimeout(null)
            ->setTty(Process::isTtySupported())
            ->run()
        ;

        if (!$process->isSuccessful()) {
            return Command::FAILURE;
        }

        $output->writeln(['<info>Docker is running...</info>', '']);

        return Command::SUCCESS;
    }

}
