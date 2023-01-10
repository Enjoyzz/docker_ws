<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http;


use Enjoys\DockerWs\Configurator\Choice\None;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class HttpServer extends Command implements SelectableService
{
    private ?ServiceInterface $service = null;

    public function getSelectedService(): ?ServiceInterface
    {
        return $this->service;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select WebServer(defaults to none)',
            // choices can also be PHP objects that implement __toString() method
            [
                new NullService(),
                new Nginx\Nginx(),
                new Apache\Apache()
            ],
            2
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        $service = $helper->ask($input, $output, $question);
        $output->writeln('You have Webserver selected: ' . $service);


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
    }
}