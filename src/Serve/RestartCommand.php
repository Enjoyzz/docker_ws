<?php

namespace Enjoys\DockerWs\Serve;

use Enjoys\Dotenv\Dotenv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'serve:reload',
    description: '',
    aliases: ['rerun', 'restart']
)]
class RestartCommand extends Serve
{

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $down = $this->getApplication()->find('serve:down');
        $up = $this->getApplication()->find('serve:up');
        $returnCode = $down->run(new ArrayInput([]), $output);
        if ($returnCode === Command::FAILURE){
            return Command::FAILURE;
        }
        $returnCode = $up->run(new ArrayInput([]), $output);
        if ($returnCode === Command::FAILURE){
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
