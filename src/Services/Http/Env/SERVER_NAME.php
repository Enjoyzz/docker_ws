<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Env;


use Enjoys\DockerWs\Env;

final class SERVER_NAME extends Env
{
    protected string $name = 'SERVER_NAME';
    protected ?string $default = 'localhost';
    protected bool $required = true;
}
