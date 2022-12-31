<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Choice;


final class None
{
    public function __toString(): string
    {
        return 'none';
    }
}