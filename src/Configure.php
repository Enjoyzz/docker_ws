<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Envs\EnvInterface;
use Enjoys\DockerWs\Services\Apache;
use Enjoys\DockerWs\Services\Mysql57;
use Enjoys\DockerWs\Services\Mysql80;
use Enjoys\DockerWs\Services\Nginx;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\Php;
use Enjoys\DockerWs\Services\ServiceInterface;
use Enjoys\DotenvWriter\DotenvWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;
use function Enjoys\FileSystem\removeDirectoryRecursive;
use function Enjoys\FileSystem\writeFile;

#[AsCommand(name: 'configure')]
final class Configure extends Command
{

    protected function configure()
    {
        $this
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $formatter = $this->getHelper('formatter');

        $output->writeln(
            $formatter->formatBlock(
                ['Docker compose configuration'],
                'bg=blue;fg=white',
                true
            )
        );

        if ($input->getOption('path') !== getenv('ROOT_PATH')) {
            $this->setRootPath($input, $output);
        }

//if (file_exists(Variables::$rootPath .'/docker-compose.yml')){
//    throw new \RuntimeException('Настройка уже была произведена, для новой настройки удалите файл docker-compose.yml');
//}
        createDirectory(getenv('ROOT_PATH') . '/docker');
        removeDirectoryRecursive(getenv('ROOT_PATH') . '/docker');

        $this->addPhpService($input, $output);
        $this->addHttpServerService($input, $output);
        $this->addDatabaseServerService($input, $output);

        $this->copyFilesInRootDirectory();
        $this->createDockerEnv($input, $output);
        $this->buildDockerComposeFile();
        $this->writeDockerServicesSummary();

        $output->writeln(['', '<fg=green;options=bold>Docker Compose has been setting</>', '']);

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    private function setRootPath(InputInterface $input, OutputInterface $output): void
    {
        $questionHelper = $this->getHelper('question');
        $question = new Question(
            sprintf(
                ' <info>%s</info> [<comment>%s</comment>]:',
                'Введите ROOT_PATH директорию',
                getenv('ROOT_PATH') ?: './'
            ),
            getenv('ROOT_PATH') ?: './'
        );

        $rootPath = realpath(trim($questionHelper->ask($input, $output, $question)));
        if (!is_string($rootPath)) {
            throw new \RuntimeException('Invalid ROOT_PATH variable');
        }
        putenv(sprintf('ROOT_PATH=%s', $rootPath));

        createDirectory(getenv('ROOT_PATH'));
        $output->writeln(getenv('ROOT_PATH'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function addPhpService(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select PHP version (defaults to 8.1)',
            // choices can also be PHP objects that implement __toString() method
            ['7.3', '7.4', '8.0', '8.1', '8.2'],
            3
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        $phpVersion = $helper->ask($input, $output, $question);
        $output->writeln('You have php version choose: ' . $phpVersion);

        $service = new Php($phpVersion);

        $this->setNewNameForService($service, $input, $output);

        DockerCompose::addService($service);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function addHttpServerService(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select WebServer(defaults to none)',
            // choices can also be PHP objects that implement __toString() method
            [new NullService(), new Nginx(), new Apache()],
            0
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        $service = $helper->ask($input, $output, $question);
        $output->writeln('You have Webserver selected: ' . $service);


        if ($service instanceof NullService) {
            return;
        }

        $this->setNewNameForService($service, $input, $output);

        DockerCompose::addService($service);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function addDatabaseServerService(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Database server (defaults to none)',
            // choices can also be PHP objects that implement __toString() method
            [
                new NullService(),
                new Mysql57(),
                new Mysql80()
            ],
            0
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        /** @var ServiceInterface $service */
        $service = $helper->ask($input, $output, $question);
        $output->writeln('You have Webserver selected: ' . $service);

        if ($service instanceof NullService) {
            return;
        }

        $this->setNewNameForService($service, $input, $output);

        DockerCompose::addService($service);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function buildDockerComposeFile(): void
    {
        writeFile(
            getenv('ROOT_PATH') . '/docker-compose.yml',
            DockerCompose::build()
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function copyFilesInRootDirectory(): void
    {
        copyDirectoryWithFilesRecursive(__DIR__ . '/../files' . '/.data', getenv('ROOT_PATH') . '/.data');
//        CreateSymlink(getenv('ROOT_PATH') . '/bin/docker', __DIR__.'/../bin/docker-ws');
    }

    /**
     * @param ServiceInterface $service
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function setNewNameForService(
        ServiceInterface $service,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $helper = $this->getHelper('question');
        $question = new Question(
            sprintf(
                ' Введите новое имя сервера (если надо изменить) [<comment>%s</comment>]:',
                $service->getName()
            ), $service->getName()
        );
        $name = $helper->ask($input, $output, $question);

        $output->writeln('');

        $service->setName($name);
    }

    private function createDockerEnv(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        $output->writeln([
                '',
                $formatter->formatBlock(
                    ['Setup variables used docker-compose.yml...'],
                    'options=bold',
                )
            ]
        );

        $envs = [];
        /** @var ServiceInterface $service */
        foreach (DockerCompose::getServices() as $service) {
            $envs = array_unique(array_merge_recursive($envs, $service->getUsedEnvKeys()));
        }

        $helper = $this->getHelper('question');

        @unlink(getenv('ROOT_PATH') . '/.docker.env');
        $dotEnvWriter = new DotenvWriter(getenv('ROOT_PATH') . '/.docker.env');


        /** @var class-string<EnvInterface> $envClassString */
        foreach ($envs as $envClassString) {
            $env = new $envClassString();
            $question = new Question(
                $env->getQuestionString(),
                $env->getDefault()
            );

            $autocompleter = $env->getAutocompleter();
            if (is_iterable($autocompleter)) {
                $question->setAutocompleterValues($autocompleter);
            } else {
                $question->setAutocompleterCallback($autocompleter);
            }

            $question->setValidator($env->getValidator());
            $question->setNormalizer($env->getNormalizer());

            $envValue = $helper->ask($input, $output, $question);

            if ($envValue === null) {
                continue;
            }
            $dotEnvWriter->setEnv($env->getName(), $envValue);
        }

        $dotEnvWriter->save();
    }

    /**
     * @throws \Exception
     */
    private function writeDockerServicesSummary()
    {
        $services = [];
        foreach (DockerCompose::getServices() as $serviceClassString => $service) {
            switch ($serviceClassString) {
                case Php::class:
                    $services['php'] = $service->getName();
                    break;
                case Mysql57::class:
                    $services['db'] = $service->getName();
                    break;
                case Nginx::class:
                case Apache::class:
                    $services['http'] = $service->getName();
                    break;
            }
        }
        writeFile(
            getenv('ROOT_PATH') . '/.docker.services',
            json_encode($services)
        );
    }

}
