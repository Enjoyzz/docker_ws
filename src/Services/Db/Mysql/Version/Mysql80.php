<?php

declare(strict_types=1);

namespace Enjoys\DockerWs\Services\Db\Mysql\Version;



use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_DATABASE;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_PASSWORD;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_USER;
use Enjoys\DockerWs\Services\ServiceInterface;

final class Mysql80 implements ServiceInterface
{
    private string $serviceName = 'mysql';

    private const USED_ENV_KEYS = [
        MYSQL_USER::class,
        MYSQL_PASSWORD::class,
        MYSQL_DATABASE::class,
        TZ::class
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }


    private array $configuration = [
        'image' => 'mysql:8.0',
        'volumes' => [
            './.data/mysql/8.0/data:/var/lib/mysql',
            './.data/mysql/8.0/conf.d:/etc/mysql/conf.d',
            './.data/mysql/8.0/logs:/var/log/mysql',
            './.data/mysql/dump:/dump',
        ],
        'ports' => [
            '4308:3306',
        ],
        'security_opt' => [
            'seccomp:unconfined',
        ],
        'environment' => [
            'MYSQL_USER' => '${MYSQL_USER:-user}',
            'MYSQL_PASSWORD' => '${MYSQL_PASSWORD:-pass}',
            'MYSQL_DATABASE' => '${MYSQL_DATABASE:-dbname}',
            'MYSQL_RANDOM_ROOT_PASSWORD' => 'yes',
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
        return '8.0.*';
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

    public function _after()
    {
        // TODO: Implement _after() method.
    }

    public function _before()
    {
        // TODO: Implement _before() method.
    }
}
