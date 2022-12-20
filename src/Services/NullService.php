<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


final class NullService implements ServiceInterface
{
    private string $name;
    public const USED_ENV_KEYS = [];


    public function __construct(string $name = null)
    {
        $this->name = $name ?? 'none';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function before()
    {

    }

    public function after()
    {

    }

    public function getConfiguration()
    {
        return [];
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}