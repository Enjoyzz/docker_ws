<?php

declare(strict_types=1);

namespace Enjoys\DockerWs\Configurator\Services\Database\Postgres;

use Enjoys\DockerWs\Configurator\Envs\DatabaseName;
use Enjoys\DockerWs\Configurator\Envs\DatabasePass;
use Enjoys\DockerWs\Configurator\Envs\DatabaseUser;
use Enjoys\DockerWs\Configurator\ServiceInterface;
use Enjoys\DockerWs\Configurator\Services\Database\DatabaseInterface;

final class v15 implements ServiceInterface, DatabaseInterface
{
    private string $name = 'postgresql';

    public function __toString(): string
    {
        return '15.x';
    }

    private const USED_ENV_KEYS = [
        DatabaseUser::class,
        DatabasePass::class,
        DatabaseName::class
    ];

    private array $configuration = [
        'image' => 'postgres:15',
        'volumes' => [
            './.data/postgres/15:/var/lib/postgresql/data',
            './.data/postgres/dump:/dump',
        ],
        'ports' => [
            '5432:5432',
        ],
        'restart' => 'unless-stopped',
        'environment' => [
            'POSTGRES_USER' => '${DATABASE_USER:-user}',
            'POSTGRES_PASSWORD' => '${DATABASE_PASS:-pass}',
            'POSTGRES_DB' => '${DATABASE_NAME:-dbname}',
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
