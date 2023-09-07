<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Env;


use Enjoys\DockerWs\Env;

final class POSTGRES_PASSWORD extends Env
{
    protected string $name = 'POSTGRES_PASSWORD';
    protected ?string $default = 'pass';
    protected bool $required = true;
}