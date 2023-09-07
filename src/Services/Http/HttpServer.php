<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http;


use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
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

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        /** @var ServiceInterface[]|SelectableService[] $choices */
        $choices =   [
            new NullService(),
            new Nginx\Nginx(),
            new Apache\Apache()
        ];
        $default = 1;
        $question = new ChoiceQuestion(
            sprintf('Select WebServer (defaults to %s)', $choices[$default]?->__toString()),
            // choices can also be PHP objects that implement __toString() method
            $choices,
            $default
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        /** @var ServiceInterface|SelectableService $service */
        $service = $helper->ask($input, $output, $question);
        $output->writeln('You have Webserver selected: ' . $service);


        if ($service instanceof NullService) {
            return;
        }

        if ($service instanceof SelectableService  && $service instanceof Command) {
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

    public function __toString(): string
    {
        return 'Choose Http Server';
    }
}