<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Postgres;


use Enjoys\DockerWs\Envs\DatabaseName;
use Enjoys\DockerWs\Envs\DatabasePass;
use Enjoys\DockerWs\Envs\DatabaseUser;
use Enjoys\DockerWs\Services\ServiceInterface;

final class v14 implements ServiceInterface
{
    private string $name = 'postgresql';

    public function __toString(): string
    {
        return '14.x';
    }

    private const USED_ENV_KEYS = [
        DatabaseUser::class,
        DatabasePass::class,
        DatabaseName::class
    ];

    private array $configuration = [
        'image' => 'postgres:14',
        'volumes' => [
            './.data/postgres/dump:/dump',
        ],
        'ports' => [
            '5432:5432',
        ],
        'restart' => 'unless-stopped',
        'environment' => [
            'POSTGRES_DB' => '${DATABASE_NAME}',
            'POSTGRES_USER' => '${DATABASE_USER}',
            'POSTGRES_PASSWORD' => '${DATABASE_PASS}',
        ],
        'networks' => [
            'backend',
        ],

    ];


    public function getName(): string
    {
        return $this->name;
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
