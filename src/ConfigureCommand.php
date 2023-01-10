<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Services\Db\Database;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql57;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql80;
use Enjoys\DockerWs\Services\Http\Apache\Version\Apache_v2_4;
use Enjoys\DockerWs\Services\Http\HttpServer;
use Enjoys\DockerWs\Services\Http\Nginx\Nginx;
use Enjoys\DockerWs\Services\Php\Php;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Enjoys\DotenvWriter\DotenvWriter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function Enjoys\FileSystem\createDirectory;
use function Enjoys\FileSystem\removeDirectoryRecursive;
use function Enjoys\FileSystem\writeFile;

final class ConfigureCommand extends Command
{
    private FormatterHelper $helperFormatter;
    private QuestionHelper $helperQuestion;


    private array $registeredCommands = [
        Php::class,
        HttpServer::class,
        Database::class
    ];
    private Application $application;


    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE)
            ->addOption('confirm-configure', 'y', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->application = $this->getApplication();
        $this->helperFormatter = $this->getHelper('formatter');
        $this->helperQuestion = $this->getHelper('question');

        $output->writeln(
            $this->helperFormatter->formatBlock(
                ['Docker workspace configuration'],
                'bg=blue;fg=white',
                true
            )
        );

        if (!$input->getOption('confirm-configure')) {
            $question = new ConfirmationQuestion(' Configure docker workspace? [<comment>Y/n</comment>]: ', true);

            if (false === $this->helperQuestion->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $this->setRootPath($input, $output);


        if (!$input->getOption('force') && file_exists(getenv('ROOT_PATH') . '/docker-compose.yml')) {
            $output->writeln(
                $this->helperFormatter->formatBlock(
                    ['Configuration is not possible, or delete the docker-compose.yml, or run the command with the --force flag'],
                    'bg=red;fg=white',
                    true
                )
            );
            return Command::SUCCESS;
        }


        removeDirectoryRecursive(getenv('ROOT_PATH') . '/.docker');


        foreach ($this->registeredCommands as $commandClassString) {
            /** @var Command&SelectableService $command */
            $command = new $commandClassString();
            if (!$command instanceof SelectableService) {
                throw new \RuntimeException(
                    sprintf('%s must be implement %s', $command::class, SelectableService::class)
                );
            }
            $command->setApplication($this->application);
            $command->execute($input, $output);

            DockerCompose::addService($command->getSelectedService());
        }

        $this->createDockerEnv($input, $output);
        $this->setNewNameForServices($input, $output);

        $this->buildDockerComposeFile();
        $this->writeDockerServicesSummary();



        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    private function setRootPath(InputInterface $input, OutputInterface $output)
    {
        $question = new Question(
            sprintf(
                ' <info>%s</info> [<comment>%s</comment>]:',
                'Введите ROOT_PATH директорию',
                getenv('ROOT_PATH') ?: './'
            ),
            getenv('ROOT_PATH') ?: './'
        );

        createDirectory($rootPath = trim($this->helperQuestion->ask($input, $output, $question)));
        $rootPath = realpath($rootPath);
        if (!is_string($rootPath)) {
            throw new \RuntimeException('Invalid ROOT_PATH variable');
        }
        putenv(sprintf('ROOT_PATH=%s', $rootPath));
        putenv(sprintf('DOCKER_PATH=%s', $rootPath . '/.docker'));

        $output->writeln('ROOT_PATH: <comment>' . getenv('ROOT_PATH') . '</comment>');
        $output->writeln('DOCKER_PATH: <comment>' . getenv('DOCKER_PATH') . '</comment>');
    }

    /**
     * @throws \Exception
     */
    private function createDockerEnv(InputInterface $input, OutputInterface $output)
    {

        $output->writeln([
                '',
                $this->helperFormatter->formatBlock(
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

        writeFile($path = getenv('DOCKER_PATH') . '/.env.docker');
        $dotEnvWriter = new DotenvWriter($path);


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

            $envValue = $this->helperQuestion->ask($input, $output, $question);

            if ($envValue === null) {
                continue;
            }
            $dotEnvWriter->setEnv($env->getName(), $envValue);
        }

        $dotEnvWriter->save();
    }

    private function setNewNameForServices(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $helper = $this->getHelper('question');

        $output->writeln(['']);
        $question = new ConfirmationQuestion(' <options=bold>Rename services names?</>[<comment>y/N</comment>]', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        foreach (DockerCompose::getServices() as $service) {
            $question = new Question(
                sprintf(
                    ' Введите новое имя сервера (если надо изменить) [<comment>%s</comment>]:',
                    $service->getServiceName()
                ), $service->getServiceName()
            );
            $name = $helper->ask($input, $output, $question);

            $output->writeln('');

            $service->setServiceName($name);
        }
    }

    /**
     * @throws \Exception
     */
    private function buildDockerComposeFile(): void
    {
        writeFile(
            getenv('DOCKER_PATH') . '/docker-compose.yml',
            \Enjoys\DockerWs\DockerCompose::build()
        );
    }

    private function writeDockerServicesSummary()
    {
        $services = [];
        foreach (DockerCompose::getServices() as $serviceClassString => $service) {
            switch ($serviceClassString) {
                case PhpService::class:
                    $services['php'] = $service->getServiceName();
                    break;
                case Mysql57::class:
                case Mysql80::class:
                    $services['db'] = $service->getServiceName();
                    break;
                case Nginx::class:
                case Apache_v2_4::class:
                    $services['http'] = $service->getServiceName();
                    break;
            }
        }
        writeFile(
            getenv('DOCKER_PATH') . '/.services',
            json_encode($services)
        );
    }

}