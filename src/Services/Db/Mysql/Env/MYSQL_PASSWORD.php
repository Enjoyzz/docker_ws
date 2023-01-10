<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\Mysql\Env;


use Enjoys\DockerWs\Env;

final class MYSQL_PASSWORD extends Env
{
    protected string $name = 'MYSQL_PASSWORD';
    protected ?string $default = 'pass';
    protected bool $required = false;
}