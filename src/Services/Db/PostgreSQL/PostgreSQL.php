<?php

namespace Enjoys\DockerWs\Services\Db\PostgreSQL;


use Enjoys\DockerWs\Services\Db\PostgreSQL\Version;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PostgreSQL extends Command implements SelectableService
{

    private ?ServiceInterface $service = null;

    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }

    public function __toString(): string
    {
        return 'PostgreSQL';
    }


    public function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Version of PostgreSQL',
            [
                new NullService('back'),
                new Version\PostgreSQL11(),
                new Version\PostgreSQL12(),
                new Version\PostgreSQL13(),
                new Version\PostgreSQL14(),
                new Version\PostgreSQL15(),
            ],
            0
        );
        $question->setErrorMessage('Choice %s is invalid.');

        $this->service = $helper->ask($input, $output, $question);
        return Command::SUCCESS;
    }


}
