<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


final class ServerName extends EnvAbstract
{
    protected string $name = 'SERVER_NAME';
    protected ?string $default = 'localhost';
    protected bool $required = true;
}
