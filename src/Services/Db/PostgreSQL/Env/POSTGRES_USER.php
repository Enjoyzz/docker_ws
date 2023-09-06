<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Env;


use Enjoys\DockerWs\Env;

final class POSTGRES_USER extends Env
{
    protected string $name = 'POSTGRES_USER';
    protected ?string $default = 'user';
    protected bool $required = true;
}
