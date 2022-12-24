<?php

namespace Enjoys\DockerWs\Serve;

use Enjoys\Dotenv\Dotenv;
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
        $formatter = $this->getHelper('formatter');

        $dotenv = new Dotenv((getenv('ROOT_PATH') ?: '.') . '/.docker.env');
        $dotenv->loadEnv();

        $_ENV['TZ'] = $_ENV['TZ'] ?? 'UTC';
        $_ENV['USER_NAME'] = $_ENV['USER_NAME'] ?? trim(
            Process::fromShellCommandline('id -un')->mustRun()->getOutput()
        );
        $_ENV['USER_ID'] = $_ENV['USER_ID'] ?? trim(Process::fromShellCommandline('id -u')->mustRun()->getOutput());
        $_ENV['WORK_DIR'] = $_ENV['WORK_DIR'] ?? '/var/www';

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
            ->setTty(true)
            ->run()
        ;

        if (!$process->isSuccessful()) {
            return Command::FAILURE;
        }

//        $output->writeln($process->getOutput(), OutputInterface::VERBOSITY_VERBOSE);
//        $output->writeln($process->getErrorOutput(), OutputInterface::VERBOSITY_VERY_VERBOSE);

        $output->writeln(
            $formatter->formatBlock(
                ['Run docker container'],
                'bg=green;fg=white'
            )
        );

        return Command::SUCCESS;
    }

}
