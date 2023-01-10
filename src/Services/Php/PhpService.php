<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Envs\WORK_DIR;
use Enjoys\DockerWs\Services\Http\Env\PUBLIC_DIR;
use Enjoys\DockerWs\Services\Http\Env\SERVER_NAME;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;

final class PhpService implements ServiceInterface
{
    private string $serviceName = 'php';

    private array $configuration = [
        'build' => [
            'context' => './.docker',
            'dockerfile' => 'php/Dockerfile',
            'args' => [
                'TZ' => '${TZ}',
                'WORK_DIR' => '${WORK_DIR}',
                'USER' => '${USER}',
                'UID' => '${UID}',
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