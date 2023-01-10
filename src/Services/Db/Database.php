<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db;


use Enjoys\DockerWs\Configurator\Choice\Back;
use Enjoys\DockerWs\Configurator\Choice\None;
use Enjoys\DockerWs\Configurator\Versioned;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class Database extends Command implements SelectableService
{

    private ?ServiceInterface $service = null;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Database server (defaults to none)',
            [
                new NullService(),
                new Mysql\Mysql()
            ],
            0
        );
        $question->setErrorMessage('%s is invalid.');

        /** @var None|Versioned|\Enjoys\DockerWs\Configurator\ServiceInterface $service */
        $service = $helper->ask($input, $output, $question);

        if ($service instanceof NullService) {
            return;
        }

        if ($service instanceof SelectableService) {
            $dbname = $service->__toString();
            $service->setApplication($this->getApplication());
            $service->execute($input, $output);
            $service = $service->getSelectedService();

            if ($service instanceof NullService && $service->getServiceName() === 'back') {
                $this->execute($input, $output);
                return;
            }
        }

        $this->service = $service;

        $output->writeln(sprintf('Chosen Database Server: <options=bold>%s %s</>', $dbname, $service));
    }



    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }
}