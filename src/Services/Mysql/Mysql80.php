<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Mysql;


use Enjoys\DockerWs\Envs\DatabaseName;
use Enjoys\DockerWs\Envs\DatabasePass;
use Enjoys\DockerWs\Envs\DatabaseUser;
use Enjoys\DockerWs\Envs\Tz;
use Enjoys\DockerWs\Services\ServiceInterface;

final class Mysql80 implements ServiceInterface
{
    private string $name = 'mysql';

    /**
     * true - required
     * false - not required
     */
    private const USED_ENV_KEYS = [
        DatabaseUser::class,
        DatabasePass::class,
        DatabaseName::class,
        Tz::class
    ];

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
            'MYSQL_USER' => '${DATABASE_USER}',
            'MYSQL_PASSWORD' => '${DATABASE_PASS}',
            'MYSQL_DATABASE' => '${DATABASE_NAME}',
            'MYSQL_RANDOM_ROOT_PASSWORD' => true,
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
        return '8.0.*';
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
