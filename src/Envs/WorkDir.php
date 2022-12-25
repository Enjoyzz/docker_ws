<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;


final class WorkDir extends EnvAbstract
{
    protected string $name = 'WORK_DIR';
    protected ?string $default = '/var/www';
    protected bool $required = false;

}
