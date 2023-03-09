<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Composer\Semver\VersionParser;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class Php extends Command implements SelectableService
{

    private const PHP_VERSIONS = [
        '8.2',
        '8.1',
        '8.0',
        '7.4',
        '7.3',
    ];

    private ?ServiceInterface $service = null;

    private function getChoices(string $constraint = null): array
    {
        if ($constraint === null) {
            return self::PHP_VERSIONS;
        }

        return array_filter(self::PHP_VERSIONS, function ($version) use ($constraint) {
            $versionParser = new VersionParser();
            $actualConstraint = $versionParser->parseConstraints($version);
            $requiredConstraint = $versionParser->parseConstraints($constraint);
            return $actualConstraint->matches($requiredConstraint);
        });
    }


    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $choices = $this->getChoices($input->getOption('php'));
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            sprintf('Select PHP version (defaults to %s)', current($choices)),
            $choices,
            key($choices)
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