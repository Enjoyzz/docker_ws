<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


class NullService implements ServiceInterface
{


    private string $serviceName;
    private string $type;

    public function __construct(string $serviceName = 'none', string $type = '')
    {
        $this->serviceName = $serviceName;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->serviceName;
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function _after()
    {
    }

    public function _before()
    {
    }


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getUsedEnvKeys(): array
    {
        return [];
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    private string $dependOnCondition = 'service_started';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }

}