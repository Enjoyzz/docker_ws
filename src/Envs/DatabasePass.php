<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;


final class DatabasePass extends EnvAbstract
{
    protected string $name = 'DATABASE_PASS';
    protected ?string $default = 'pass';
    protected bool $required = true;
}
