<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Services\Database\Mysql;



use Enjoys\DockerWs\Configurator\Envs\DatabaseName;
use Enjoys\DockerWs\Configurator\Envs\DatabasePass;
use Enjoys\DockerWs\Configurator\Envs\DatabaseUser;
use Enjoys\DockerWs\Configurator\Envs\Tz;
use Enjoys\DockerWs\Configurator\ServiceInterface;
use Enjoys\DockerWs\Configurator\Services\Database\DatabaseInterface;

final class Mysql57 implements ServiceInterface, DatabaseInterface
{
    private string $name = 'mysql';

    private const USED_ENV_KEYS = [
        DatabaseUser::class,
        DatabasePass::class,
        DatabaseName::class,
        Tz::class
    ];

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
            'MYSQL_USER' => '${DATABASE_USER:-user}',
            'MYSQL_PASSWORD' => '${DATABASE_PASS:-pass}',
            'MYSQL_DATABASE' => '${DATABASE_NAME:-dbname}',
            'MYSQL_RANDOM_ROOT_PASSWORD' => 'yes',
            'TZ' => '${TZ}',
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
        return  '5.7.*';
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }

    public function before()
    {
    }

    public function after()
    {
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
