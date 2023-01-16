<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


interface ServiceInterface
{

    public function getConfiguration();

    public function getType(): string;

    public function getDependsOnCondition(): string;

    public function _after();

    public function _before();

    public function getUsedEnvKeys(): array;

    public function getServiceName(): string;

    public function setServiceName(string $serviceName): void;
}