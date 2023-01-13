<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;


use Enjoys\DockerWs\Env;

final class WORK_DIR extends Env
{
    protected string $name = 'WORK_DIR';
    protected ?string $default = '/var/www';
    protected bool $required = false;
}
