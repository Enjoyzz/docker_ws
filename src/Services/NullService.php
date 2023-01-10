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

    public function __toString(): string
    {
        return $this->serviceName;
    }

    public function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
    }

    public function _after()
    {
        // TODO: Implement _after() method.
    }

    public function _before()
    {
        // TODO: Implement _before() method.
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
        // TODO: Implement setServiceName() method.
    }
}