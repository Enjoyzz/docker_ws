<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Nginx;


use Enjoys\DockerWs\DockerCompose;
use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Envs\WORK_DIR;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql57;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql80;
use Enjoys\DockerWs\Services\Http\Env\PUBLIC_DIR;
use Enjoys\DockerWs\Services\Http\Env\SERVER_NAME;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;

final class Nginx implements ServiceInterface
{
    private string $serviceName = 'nginx';

    public function getType(): string
    {
        return 'nginx';
    }


    private string $dependOnCondition = 'service_started';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }


    private const POSSIBLE_DEPEND_SERVICES = [
        PhpService::class,
        Mysql80::class,
        Mysql57::class
    ];

    private const USED_ENV = [
        SERVER_NAME::class,
        TZ::class,
        WORK_DIR::class,
        PUBLIC_DIR::class,
    ];

    private array $configuration = [
        'image' => 'nginx:1.19-alpine',
        'ports' => [
            '80:80',
            '8000:8000'
        ],
        'volumes' => [
            './nginx/templates:/etc/nginx/templates',
            './.data/logs/nginx:/var/log/nginx',
            './..:${WORK_DIR}',
        ],
        'depends_on' => [],
        'environment' => [
            'TZ' => '${TZ}',
            'PUBLIC_DIR' => '${PUBLIC_DIR}',
            'SERVER_NAME' => '${SERVER_NAME:-localhost}',
            'FASTCGI_PASS' => 'php:9000'
        ],
        'networks' => [
            'backend',
        ],

    ];


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function __toString(): string
    {
        return $this->getServiceName();
    }


    /**
     * @return mixed
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV;
    }


    public function _after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/images',
            getenv('DOCKER_PATH') . '/nginx'
        );

        createDirectory(getenv('DOCKER_PATH') . '/.data/logs/nginx');
    }

    public function _before()
    {
        $registeredServices = DockerCompose::getServices(true);


        foreach ($registeredServices as $service) {
            if (in_array($service, self::POSSIBLE_DEPEND_SERVICES, true)) {
                $serviceClass = DockerCompose::getServiceByKey($service);
                $this->configuration['depends_on'][$serviceClass->getServiceName(
                )]['condition'] = $serviceClass->getDependsOnCondition();
            }
        }

        $phpService = DockerCompose::getServiceByKey(PhpService::class);
        $this->configuration['environment']['FASTCGI_PASS'] = sprintf('%s:9000', $phpService->getServiceName());

        if (empty($this->configuration['depends_on'])) {
            unset($this->configuration['depends_on']);
        }
    }
}
