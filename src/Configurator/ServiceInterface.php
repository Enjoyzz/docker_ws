<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator;


interface ServiceInterface
{



    public function getName();

    public function before();

    public function after();

    public function getConfiguration();

    public function setName(string $name);

    public function getUsedEnvKeys(): array;
}