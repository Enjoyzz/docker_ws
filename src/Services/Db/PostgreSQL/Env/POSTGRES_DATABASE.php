<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Env;


use Enjoys\DockerWs\Env;

final class POSTGRES_DATABASE extends Env
{
    protected string $name = 'POSTGRES_DATABASE';
    protected ?string $default = 'dbname';
    protected bool $required = true;
}