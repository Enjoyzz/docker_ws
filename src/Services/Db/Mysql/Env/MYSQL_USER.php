<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\Mysql\Env;


use Enjoys\DockerWs\Env;

final class MYSQL_USER extends Env
{
    protected string $name = 'MYSQL_USER';
    protected ?string $default = 'user';
    protected bool $required = false;
}
