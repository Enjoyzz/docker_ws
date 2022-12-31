<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


final class DatabaseName extends EnvAbstract
{
    protected string $name = 'DATABASE_NAME';
    protected ?string $default = 'dbname';
    protected bool $required = true;
}
