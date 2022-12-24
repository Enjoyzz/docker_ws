<?php

namespace Enjoys\DockerWs\Serve;

use Enjoys\Dotenv\Dotenv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'serve:down',
    description: '',
    aliases: ['down', 'stop']
)]
class DownCommand extends Serve
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process([
            'docker-compose',
            '--file',
            (getenv('ROOT_PATH') ?: '.') . '/docker-compose.yml',
            '--env-file',
            (getenv('ROOT_PATH') ?: '.') . '/.docker.env',
            'down',
        ]);

        $process->setTimeout(null)
            ->setTty(true)
            ->run()
        ;

        if (!$process->isSuccessful()) {
            return Command::FAILURE;
        }

        $output->writeln(['<info>Docker is stopped...</info>', '']);

        return Command::SUCCESS;
    }

}
