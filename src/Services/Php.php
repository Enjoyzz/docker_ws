<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


use Enjoys\DockerWs\Variables;
use Symfony\Component\Yaml\Yaml;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Php implements ServiceInterface
{
    private string $name = 'php';

    public const USED_ENV_KEYS = [
        'WORK_DIR',
        'TZ',
        'USER_NAME',
        'USER_ID'
    ];

    protected array $configuration = [
        'container_name' => 'php',
        'build' => [
            'context' => './docker/php',
            'dockerfile' => 'Dockerfile',
            'args' => [
                'TZ' => '${TZ}',
                'WORK_DIR' => '${WORK_DIR}',
                'USER_NAME' => '${USER_NAME}',
                'USER_ID' => '${USER_ID}',
            ]
        ],
        'working_dir' => '${WORK_DIR}',
        'volumes' => [
            './.data/ssh:/home/${USER_NAME}/.ssh',
            './.data/cache:/home/${USER_NAME}/.cache',
            './.data/composer:/home/${USER_NAME}/.composer',
            '/.data/mail:/home/mail',
            './:${WORK_DIR}',
        ],
        'ports' => [
            '9006:9000'
        ],
        'networks' => [
            'backend'
        ]
    ];


    public function __construct(private string $phpVersion)
    {
        $this->setPhpVersion($phpVersion);
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function setPhpVersion(string $phpVersion): void
    {
        $this->phpVersion = $phpVersion;
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/../../docker/php/' . $this->phpVersion,
            Variables::$rootPath . '/docker/php'
        );
        copy(__DIR__ . '/../../docker/php/alias.sh', Variables::$rootPath . '/docker/php/alias.sh');
        copy(__DIR__ . '/../../docker/php/sendmail', Variables::$rootPath . '/docker/php/sendmail');
    }

    public function before()
    {
    }

}