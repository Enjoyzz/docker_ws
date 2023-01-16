<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Apache\Version;


use Enjoys\DockerWs\DockerCompose;
use Enjoys\DockerWs\Envs\WORK_DIR;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql57;
use Enjoys\DockerWs\Services\Db\Mysql\Version\Mysql80;
use Enjoys\DockerWs\Services\Http\Env\PUBLIC_DIR;
use Enjoys\DockerWs\Services\Http\Env\SERVER_NAME;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\createDirectory;

final class Apache_v2_4 implements ServiceInterface, \Stringable
{
    private string $serviceName = 'apache';

    public function __toString(): string
    {
        return 'Apache v2.4';
    }

    public function getType(): string
    {
        return 'apache';
    }

    private const POSSIBLE_DEPEND_SERVICES = [
        PhpService::class,
        Mysql80::class,
        Mysql57::class
    ];

    private string $dependOnCondition = 'service_started';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }


    private array $configuration = [
        'build' => [
            'context' => './',
            'dockerfile' => 'apache/Dockerfile'
        ],
        'environment' => [
            'PUBLIC_DIR' => '${PUBLIC_DIR}',
            'SERVER_NAME' => '${SERVER_NAME:-localhost}',
            'FASTCGI_PASS' => 'php:9000',
            'LISTEN' => 80,
        ],

        'volumes' => [
            './.data/logs/apache/:/var/log/',
            './..:${WORK_DIR}',
        ],
        'depends_on' => [],
        'ports' => [
            '80:80',
        ],
        'networks' => [
            'backend',
        ],

    ];

    private const USED_ENV = [
        SERVER_NAME::class,
        WORK_DIR::class,
        PUBLIC_DIR::class,
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }


    public function _after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/../images',
            getenv('DOCKER_PATH') . '/apache'
        );

        createDirectory(getenv('DOCKER_PATH') . '/.data/logs/apache');
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

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }


}