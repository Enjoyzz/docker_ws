<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


final class NullService implements ServiceInterface
{


    private string $serviceName;

    public function __construct(string $serviceName = 'none')
    {
        $this->serviceName = $serviceName;
    }

    public function getType(): string
    {
        return '';
    }

    public function __toString(): string
    {
        return $this->serviceName;
    }

    public function getConfiguration()
    {
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
    }

    private string $dependOnCondition = 'service_started';

    public function getDependsOnCondition(): string
    {
        return $this->dependOnCondition;
    }

}