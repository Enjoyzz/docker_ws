<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator;


use Enjoys\DockerWs\Configurator\Choice\Back;
use Enjoys\DockerWs\Configurator\Choice\None;
use Enjoys\DockerWs\Configurator\Services;
use Enjoys\DotenvWriter\DotenvWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
            ->addOption('force', 'f', InputOption::VALUE_NONE)
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

        if (!$input->getOption('force') && file_exists( getenv('ROOT_PATH') .'/docker-compose.yml')){
            $output->writeln(
                $formatter->formatBlock(
                    ['Configuration is not possible, or delete the docker-compose.yml, or run the command with the --force flag'],
                    'bg=red;fg=white',
                    true
                )
            );
            return Command::FAILURE;
        }

        createDirectory(getenv('ROOT_PATH') . '/docker');
        removeDirectoryRecursive(getenv('ROOT_PATH') . '/docker');

        $this->addPhpService($input, $output);
        $this->addHttpServerService($input, $output);
        $this->addDatabaseServerService($input, $output);

        $this->copyFilesInRootDirectory();
        $this->createDockerEnv($input, $output);
        $this->setNewNameForServices($input, $output);

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

        createDirectory($rootPath = trim($questionHelper->ask($input, $output, $question)));
        $rootPath = realpath($rootPath);
        if (!is_string($rootPath)) {
            throw new \RuntimeException('Invalid ROOT_PATH variable');
        }
        putenv(sprintf('ROOT_PATH=%s', $rootPath));

        $output->writeln('ROOT_PATH: <comment>'.getenv('ROOT_PATH').'</comment>');
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

        $service = new Services\Php($phpVersion);

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
            [new None(), new Services\Http\Nginx(), new Services\Http\Apache()],
            0
        );
        $question->setErrorMessage('WebServer %s is invalid.');

        $service = $helper->ask($input, $output, $question);
        $output->writeln('You have Webserver selected: ' . $service);


        if ($service instanceof None) {
            return;
        }

        DockerCompose::addService($service);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function addDatabaseServerService(InputInterface $input, OutputInterface $output): void
    {
        $dbname='';
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select Database server (defaults to none)',
            // choices can also be PHP objects that implement __toString() method
            [
                new None(),
                new Services\Database\Mysql(),
                new Services\Database\Postgres()
            ],
            0
        );
        $question->setErrorMessage('%s is invalid.');

        /** @var None|Versioned|ServiceInterface $service */
        $service = $helper->ask($input, $output, $question);

        if ($service instanceof None) {
            return;
        }

        if ($service instanceof Versioned){
            $dbname = $service->__toString();
            $service = $service->selectVersion($helper, $input, $output);
            if ($service instanceof Back){
                $this->addDatabaseServerService($input, $output);
                return;
            }
        }

        $output->writeln(sprintf('Chosen Database Server: <options=bold>%s %s</>', $dbname, $service));

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
        copyDirectoryWithFilesRecursive(__DIR__ . '/../../files' . '/.data', getenv('ROOT_PATH') . '/.data');
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
                    $service->getName()
                ), $service->getName()
            );
            $name = $helper->ask($input, $output, $question);

            $output->writeln('');

            $service->setName($name);
        }

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
                case Services\Php::class:
                    $services['php'] = $service->getName();
                    break;
                case Services\Database\Mysql\Mysql57::class:
                case Services\Database\Mysql\Mysql80::class:
                case Services\Database\Postgres\v15::class:
                    $services['db'] = $service->getName();
                    break;
                case Services\Http\Nginx::class:
                case Services\Http\Apache::class:
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
