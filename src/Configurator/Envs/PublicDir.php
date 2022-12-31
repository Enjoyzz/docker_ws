<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


use Enjoys\DockerWs\Configurator\Envs\EnvAbstract;

final class PublicDir extends EnvAbstract
{
    protected string $name = 'PUBLIC_DIR';
    protected ?string $default = '${WORK_DIR}/public';
    protected bool $required = true;

}
