<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


final class DatabaseUser extends EnvAbstract
{
    protected string $name = 'DATABASE_USER';
    protected ?string $default = 'user';
    protected bool $required = true;
}
