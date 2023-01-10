<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\Mysql\Env;


use Enjoys\DockerWs\Env;

final class MYSQL_DATABASE extends Env
{
    protected string $name = 'MYSQL_DATABASE';
    protected ?string $default = 'dbname';
    protected bool $required = false;
}