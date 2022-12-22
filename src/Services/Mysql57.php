<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


final class Mysql57 implements ServiceInterface
{
    private string $name = 'mysql-5.7';

    /**
     * true - required
     * false - not required
     */
    private const USED_ENV_KEYS = [
        'DATABASE_NAME' => true,
        'DATABASE_PASS' => true,
    ];

    private array $configuration = [
        'container_name' => 'mysql',
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
            'MYSQL_DATABASE' => '${DATABASE_NAME}',
            'MYSQL_ROOT_PASSWORD' => '${DATABASE_PASS}',
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
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getConfiguration(): mixed
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