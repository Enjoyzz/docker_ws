<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


use Enjoys\DockerWs\DockerCompose;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Apache implements ServiceInterface
{
    private const POSSIBLE_DEPEND_SERVICES = [
        Php::class,
        Mysql57::class,
    ];

    /**
     * true - required
     * false - not required
     */
    private const USED_ENV_KEYS = [
        'WORK_DIR' => false,
        'SERVER_NAME' => true
    ];


    private string $name = 'apache-2.4';

    private array $configuration = [
        'build' => [
            'context' => './docker/apache',
            'dockerfile' => 'Dockerfile'
        ],
        'environment' => [
            'PUBLIC_DIR' => '${WORK_DIR}/public',
            'SERVER_NAME' => '${SERVER_NAME}',
            'FASTCGI_PASS' => 'php:9000',
            'LISTEN' => 80,
        ],

        'volumes' => [
            './.data/logs/apache/:/var/log/',
            './:${WORK_DIR}',
        ],
        'depends_on' => [],
        'ports' => [
            '80:80',
        ],
        'networks' => [
            'backend',
        ],

    ];

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }


    public function before()
    {
        $registeredServices = DockerCompose::getServices(true);

        foreach ($registeredServices as $service) {
            if (in_array($service, self::POSSIBLE_DEPEND_SERVICES, true)) {
                $this->configuration['depends_on'][] = DockerCompose::getServiceByKey($service)->getName();
            }
        }

        $phpService = DockerCompose::getServiceByKey(Php::class);
        $this->configuration['environment']['FASTCGI_PASS'] = sprintf('%s:9000', $phpService->getName());

        if (empty($this->configuration['depends_on'])) {
            unset($this->configuration['depends_on']);
        }
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(__DIR__.'/../../files/docker/apache', getenv('ROOT_PATH') . '/docker/apache');
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
