<?php

namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Version;

use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_DATABASE;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_PASSWORD;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_USER;

use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\createDirectory;

abstract class PostgreSQL implements ServiceInterface
{
    protected string $serviceName = 'postgresql';


    protected string $dependOnCondition = 'service_healthy';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }

    public function getType(): string
    {
        return 'postgresql';
    }

    protected array $USED_ENV = [
        POSTGRES_USER::class,
        POSTGRES_PASSWORD::class,
        POSTGRES_DATABASE::class,
        TZ::class
    ];

    public function getUsedEnvKeys(): array
    {
        return $this->USED_ENV;
    }

    protected array $configuration = [
        'ports' => [
            '5432:5432',
        ],
        'security_opt' => [
            'seccomp:unconfined',
        ],
        'healthcheck' => [
            'test' => ["CMD-SHELL", 'pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DATABASE}'],
            'interval' => '30s',
            'timeout' => '10s',
            'retries' => 5,
        ],
        'environment' => [
            'POSTGRES_USER' => '${POSTGRES_USER}',
            'POSTGRES_PASSWORD' => '${POSTGRES_PASSWORD}',
            'POSTGRES_DB' => '${POSTGRES_DATABASE}',
            'TZ' => '${TZ}',
        ],
        'networks' => [
            'backend',
            'postgres'
        ],

    ];


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }


    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @throws \Exception
     */
    public function _after()
    {
    }

    public function _before()
    {
    }
}