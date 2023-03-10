<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Apache;


use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class Apache extends Command implements SelectableService
{

    private ?ServiceInterface $service = null;

    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }


    public function __toString(): string
    {
        return 'Apache';
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Version of Apache',
            [
                new NullService('back'),
                new Version\Apache_v2_4(),
            ],
            1
        );
        $question->setErrorMessage('Choice %s is invalid.');

        $this->service = $helper->ask($input, $output, $question);
        return Command::SUCCESS;
    }

}
