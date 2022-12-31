<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


use Enjoys\DockerWs\DockerCompose;
use Enjoys\DockerWs\Envs\PublicDir;
use Enjoys\DockerWs\Envs\ServerName;
use Enjoys\DockerWs\Envs\Tz;
use Enjoys\DockerWs\Envs\WorkDir;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Nginx implements ServiceInterface
{
    private string $name = 'nginx';

    private const POSSIBLE_DEPEND_SERVICES = [
        Php::class,
        Mysql\Mysql80::class, Mysql\Mysql57::class,
        Postgres\v14::class, Postgres\v15::class
    ];

    private const USED_ENV_KEYS = [
        ServerName::class,
        Tz::class,
        WorkDir::class,
        PublicDir::class,
    ];

    private array $configuration = [
        'image' => 'nginx:1.19-alpine',
        'ports' => [
            '80:80',
            '8000:8000'
        ],
        'volumes' => [
            './docker/nginx/templates:/etc/nginx/templates',
            './.data/logs/nginx:/var/log/nginx',
            './:${WORK_DIR}',
        ],
        'depends_on' => [],
        'environment' => [
            'TZ' => '${TZ}',
            'PUBLIC_DIR' => '${PUBLIC_DIR}',
            'SERVER_NAME' => '${SERVER_NAME}',
            'FASTCGI_PASS' => 'php:9000'
        ],
        'networks' => [
            'backend',
        ],

    ];


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
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
        return self::USED_ENV_KEYS;
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(__DIR__.'/../../files/docker/nginx', getenv('ROOT_PATH'). '/docker/nginx');
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


}
