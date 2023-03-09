<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Envs\WORK_DIR;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;

final class PhpService implements ServiceInterface
{
    private string $serviceName = 'php';

    public function getType(): string
    {
        return 'php';
    }

    public function __toString(): string
    {
        return $this->getType();
    }


    private string $dependOnCondition = 'service_started';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }

    private array $configuration = [
        'build' => [
            'context' => './',
            'dockerfile' => 'php/Dockerfile',
            'args' => [
                'TZ' => '${TZ}',
                'WORK_DIR' => '${WORK_DIR}',
                '__UNAME' => '${__UNAME:-user}',
                '__UID' => '${__UID:-1000}',
                '__GID' => '${__GID:-1000}',
            ]
        ],
        'volumes' => [
            '/.data/mail:/home/mail',
            './..:${WORK_DIR}',
        ],
        'ports' => [
            '9006:9000'
        ],
        'networks' => [
            'backend'
        ]
    ];

    public function __construct(string $phpVersion)
    {
        $this->configuration['build']['args']['PHP_IMAGE'] = sprintf('enjoys/php:%s-fpm-alpine', $phpVersion);
    }

    private const USED_ENV = [
        TZ::class,
        WORK_DIR::class,
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @throws \Exception
     */
    public function _after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/images',
            getenv('DOCKER_PATH') . '/php'
        );

        createDirectory(getenv('DOCKER_PATH') . '/.data/mail');
    }

    public function _before()
    {
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }
}