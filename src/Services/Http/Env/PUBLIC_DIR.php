<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Env;


use Enjoys\DockerWs\Env;

final class PUBLIC_DIR extends Env
{
    protected string $name = 'PUBLIC_DIR';
    protected ?string $default = '${WORK_DIR}/public';
    protected bool $required = true;
}
