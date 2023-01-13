<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class Php extends Command implements SelectableService
{

    private const PHP_VERSIONS = [
        '8.2', '8.1', '8.0', '7.4',
        //'7.3', '7.2', '7.1', '7.0', '5.6'
    ];

    private ?ServiceInterface $service = null;


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select PHP version (defaults to 8.0)',
            self::PHP_VERSIONS,
            2
        );
        $question->setErrorMessage('Php version %s is invalid.');

        $phpVersion = $helper->ask($input, $output, $question);
        $output->writeln('Selected php version: ' . $phpVersion);
        $service = new PhpService($phpVersion);
        $this->setService($service);
    }


    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }


    private function setService(?ServiceInterface $service): void
    {
        $this->service = $service;
    }
}