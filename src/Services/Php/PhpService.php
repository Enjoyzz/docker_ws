<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Php;


use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

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
                'USER_NAME' => '${USER_NAME}',
                'USER_ID' => '${USER_ID}',
            ]
        ],
        'volumes' => [
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
    private string $phpVersion;

    public function __construct(string $phpVersion)
    {
        $this->phpVersion = $phpVersion;
        $this->configuration['build']['args']['PHP_IMAGE'] = sprintf('enjoys/php:%s-fpm-alpine', $phpVersion);
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function _after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/files',
            getenv('DOCKER_PATH') . '/php'
        );
    }

    public function _before()
    {
        // TODO: Implement _before() method.
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