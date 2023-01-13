<?php

declare(strict_types=1);

namespace Enjoys\DockerWs\Services\Db\Mysql\Version;

use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_DATABASE;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_PASSWORD;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_USER;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\createDirectory;

final class Mysql57 implements ServiceInterface
{
    private string $serviceName = 'mysql';

    private const USED_ENV = [
        MYSQL_USER::class,
        MYSQL_PASSWORD::class,
        MYSQL_DATABASE::class,
        TZ::class
    ];

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV;
    }

    private array $configuration = [
        'image' => 'mysql:5.7',
        'volumes' => [
            './.data/mysql/5.7/data:/var/lib/mysql',
            './.data/mysql/5.7/conf.d:/etc/mysql/conf.d',
            './.data/mysql/5.7/logs:/var/log/mysql',
            './.data/mysql/dump:/dump',
        ],
        'ports' => [
            '4305:3306',
        ],
        'security_opt' => [
            'seccomp:unconfined',
        ],
        'environment' => [
            'MYSQL_USER' => '${MYSQL_USER}',
            'MYSQL_PASSWORD' => '${MYSQL_PASSWORD}',
            'MYSQL_DATABASE' => '${MYSQL_DATABASE}',
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
        return '5.7.*';
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
        createDirectory(getenv('DOCKER_PATH') . '/.data/mysql/dump');
        createDirectory(getenv('DOCKER_PATH') . '/.data/mysql/5.7/conf.d');
        createDirectory(getenv('DOCKER_PATH') . '/.data/mysql/5.7/logs');
        createDirectory(getenv('DOCKER_PATH') . '/.data/mysql/5.7/data');
    }

    public function _before()
    {
    }
}
