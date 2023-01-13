<?php

namespace Enjoys\DockerWs\Services\Db\Mysql;


use Enjoys\DockerWs\Services\Db\Mysql\Back;
use Enjoys\DockerWs\Services\Db\Mysql\Version;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Mysql extends Command implements SelectableService, \Stringable
{

    private ?ServiceInterface $service = null;

    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }

    public function __toString(): string
    {
        return 'MySQL';
    }


    public function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Version of MySQL',
            [
                new NullService('back'),
                new Version\Mysql57(),
                new Version\Mysql80()
            ],
            0
        );
        $question->setErrorMessage('Choice %s is invalid.');

        $this->service = $helper->ask($input, $output, $question);
        return Command::SUCCESS;
    }


}
