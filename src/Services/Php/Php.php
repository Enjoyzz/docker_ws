<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Composer\Semver\VersionParser;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
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
        '7.2',
        '7.1',
        '7.0',
        '5.6'
    ];

    private ?ServiceInterface $service = null;

    private function getAllowedVersion(string $user_allowed_version = null): array
    {
        if ($user_allowed_version === null) {
            return self::PHP_VERSIONS;
        }
        $phpConstraint = str_replace(
            'php:',
            '',
            current(
                array_filter(array_map('trim', explode(';', $user_allowed_version)), function ($item) {
                    return str_starts_with($item, 'php:');
                })
            )
        );

        $result = array_filter(self::PHP_VERSIONS, function ($version) use ($phpConstraint) {
            $versionParser = new VersionParser();
            $actualConstraint = $versionParser->parseConstraints($version);
            $requiredConstraint = $versionParser->parseConstraints($phpConstraint);
            return $actualConstraint->matches($requiredConstraint);
        });
        return (empty($result)) ? self::PHP_VERSIONS : $result;
    }


    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $allowed_version = $this->getAllowedVersion($input->getOption('allowed-version'));
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            sprintf('Select PHP version (defaults to %s)', current($allowed_version)),
            $allowed_version,
            key($allowed_version)
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