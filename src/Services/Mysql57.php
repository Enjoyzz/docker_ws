<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


final class Mysql57 implements ServiceInterface
{
    private string $name = 'mysql-5.7';

    public const USED_ENV_KEYS = [
        'DATABASE_NAME',
        'DATABASE_PASS',
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

    public function before()
    {
        // TODO: Implement before() method.
    }

    public function after()
    {
        // TODO: Implement after() method.
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}