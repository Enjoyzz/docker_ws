<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Services\Apache;
use Enjoys\DockerWs\Services\Mysql57;
use Enjoys\DockerWs\Services\Nginx;
use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\Php;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;
use function Enjoys\FileSystem\CreateSymlink;
use function Enjoys\FileSystem\removeDirectoryRecursive;
use function Enjoys\FileSystem\writeFile;

#[AsCommand(name: 'configure')]
final class Configure extends Command
{

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $this->setRootPath($input, $output);

//if (file_exists(Variables::$rootPath .'/docker-compose.yml')){
//    throw new \RuntimeException('Настройка уже была произведена, для новой настройки удалите файл docker-compose.yml');
//}
        createDirectory(Variables::$rootPath . '/docker');
        removeDirectoryRecursive(Variables::$rootPath . '/docker');

        $this->addPhpService($input, $output);
        $this->addHttpServerService($input, $output);
        $this->addDatabaseServerService($input, $output);

        $this->copyFilesInRootDirectory();
        $this->buildDockerComposeFile();

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
                './'
            ),
            './'
        );

        $answer = $questionHelper->ask($input, $output, $question);
        Variables::$rootPath = trim($answer);
        createDirectory(Variables::$rootPath);
        $output->writeln(realpath(Variables::$rootPath));
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
            [new NullService(), new Mysql57()],
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
            Variables::$rootPath . '/docker-compose.yml',
            DockerCompose::build()
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function copyFilesInRootDirectory(): void
    {
        copyDirectoryWithFilesRecursive(__DIR__ . '/../.data', Variables::$rootPath . '/.data');
        CreateSymlink(Variables::$rootPath . '/bin/docker', __DIR__ . '/../bin/docker');
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
        $service->setName($name);
    }

}