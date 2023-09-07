<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Services\Db\Database;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql57;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql80;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Version\PostgreSQL11;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Version\PostgreSQL12;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Version\PostgreSQL13;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Version\PostgreSQL14;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Version\PostgreSQL15;
use Enjoys\DockerWs\Services\Db\SQLite\SQlite;
use Enjoys\DockerWs\Services\Http\Apache\Version\Apache_v2_4;
use Enjoys\DockerWs\Services\Http\HttpServer;
use Enjoys\DockerWs\Services\Http\Nginx\Nginx;
use Enjoys\DockerWs\Services\Php\Php;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\SelectableService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Enjoys\DotenvWriter\DotenvWriter;
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

    private array $registeredCommands = [
        Php::class,
        HttpServer::class,
        Database::class
    ];


    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE)
            ->addOption('confirm-configure', 'y', InputOption::VALUE_NONE)
            ->addOption('php', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        /** @var FormatterHelper $helperFormatter */
        $helperFormatter = $this->getHelper('formatter');
        /** @var QuestionHelper $helperQuestion */
        $helperQuestion = $this->getHelper('question');

        $output->writeln(
            $helperFormatter->formatBlock(
                ['Docker workspace configuration'],
                'bg=blue;fg=white',
                true
            )
        );

        if (!$input->getOption('confirm-configure')) {
            $question = new ConfirmationQuestion(' Configure docker workspace? [<comment>Y/n</comment>]: ', true);

            if (false === $helperQuestion->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $this->setRootPath($input, $output);


        if (!$input->getOption('force') && file_exists(getenv('DOCKER_PATH') . '/docker-compose.yml')) {
            $output->writeln(
                $helperFormatter->formatBlock(
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
            $command->setApplication($application);
            $command->execute($input, $output);

            DockerCompose::addService($command->getSelectedService());
        }

        $this->createDockerEnv($input, $output);
        $this->setNewNameForServices($input, $output);

        $this->buildDockerComposeFile();
        $this->writeDockerServicesSummary();

        copy(__DIR__.'/.gitignore.dist', getenv('DOCKER_PATH').'/.gitignore');
        copy(getenv('DOCKER_PATH').'/.env.docker', getenv('DOCKER_PATH').'/.env');
        copy(__DIR__.'/docker.mk', getenv('DOCKER_PATH').'/Makefile');

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    private function setRootPath(InputInterface $input, OutputInterface $output): void
    {
        /** @var QuestionHelper $helperQuestion */
        $helperQuestion = $this->getHelper('question');
        $question = new Question(
            sprintf(
                ' <info>%s</info> [<comment>%s</comment>]:',
                'Введите ROOT_PATH директорию',
                getenv('ROOT_PATH') ?: './'
            ),
            getenv('ROOT_PATH') ?: './'
        );

        createDirectory($rootPath = trim($helperQuestion->ask($input, $output, $question)));
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
    private function createDockerEnv(InputInterface $input, OutputInterface $output): void
    {
        /** @var FormatterHelper $helperFormatter */
        $helperFormatter = $this->getHelper('formatter');
        /** @var QuestionHelper $helperQuestion */
        $helperQuestion = $this->getHelper('question');
        $output->writeln([
                '',
                $helperFormatter->formatBlock(
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

            $envValue = $helperQuestion->ask($input, $output, $question);

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
        /** @var QuestionHelper $helper */
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
            DockerCompose::build()
        );
    }

    /**
     * @throws \Exception
     */
    private function writeDockerServicesSummary(): void
    {
        $services = [];
        foreach (DockerCompose::getServices() as $serviceClassString => $service) {
            switch ($serviceClassString) {
                case PhpService::class:
                    $services['php'] = [
                        'service_name' => $service->getServiceName(),
                        'type' => $service->getType()
                    ];
                    break;
                case Mysql57::class:
                case Mysql80::class:
                case SQlite::class:
                case PostgreSQL11::class:
                case PostgreSQL12::class:
                case PostgreSQL13::class:
                case PostgreSQL14::class:
                case PostgreSQL15::class:
                    $services['db'] = [
                        'service_name' => $service->getServiceName(),
                        'type' => $service->getType()
                    ];
                    break;
                case Nginx::class:
                case Apache_v2_4::class:
                    $services['http'] = [
                        'service_name' => $service->getServiceName(),
                        'type' => $service->getType()
                    ];
                    break;
            }
        }
        writeFile(
            getenv('DOCKER_PATH') . '/.services',
            json_encode($services)
        );
    }

}