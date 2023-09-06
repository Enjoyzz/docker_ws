<?php

declare(strict_types=1);

namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Version;

use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_DATABASE;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_PASSWORD;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_USER;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_DATABASE;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_PASSWORD;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_USER;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\createDirectory;

final class PostgreSQL11 implements ServiceInterface
{
    private string $serviceName = 'postgresql';


    private string $dependOnCondition = 'service_healthy';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }

    public function getType(): string
    {
        return 'postgresql';
    }

    private const USED_ENV = [
        POSTGRES_USER::class,
        POSTGRES_PASSWORD::class,
        POSTGRES_DATABASE::class,
        TZ::class
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV;
    }

    private array $configuration = [
        'image' => 'postgres:11.21-alpine3.18',
        'volumes' => [
            './.data/postgres/11/data:/var/lib/postgresql/data',
        ],
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
        ],

    ];


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function __toString(): string
    {
        return 'v11 (11.21)';
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
        createDirectory(getenv('DOCKER_PATH') . '/.data/postgres/11/data');
    }

    public function _before()
    {
    }
}
