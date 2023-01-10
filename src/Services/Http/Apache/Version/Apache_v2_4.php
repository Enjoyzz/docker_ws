<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Apache\Version;


use Enjoys\DockerWs\DockerCompose;
use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Envs\WORK_DIR;
use Enjoys\DockerWs\Services\Http\Env\PUBLIC_DIR;
use Enjoys\DockerWs\Services\Http\Env\SERVER_NAME;
use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Apache_v2_4 implements ServiceInterface, \Stringable
{
    private string $serviceName = 'apache';

    public function __toString(): string
    {
        return 'Apache v2.4';
    }

    private const POSSIBLE_DEPEND_SERVICES = [
        PhpService::class
    ];


    private array $configuration = [
        'build' => [
            'context' => './apache',
            'dockerfile' => 'Dockerfile'
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

    private const USED_ENV_KEYS = [
        SERVER_NAME::class,
        WORK_DIR::class,
        PUBLIC_DIR::class,
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
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
    }

    public function _before()
    {
        $registeredServices = DockerCompose::getServices(true);

        foreach ($registeredServices as $service) {
            if (in_array($service, self::POSSIBLE_DEPEND_SERVICES, true)) {
                $this->configuration['depends_on'][] = DockerCompose::getServiceByKey($service)->getServiceName();
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