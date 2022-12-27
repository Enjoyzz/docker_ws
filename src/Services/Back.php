<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


final class Back implements ServiceInterface
{
    private string $name;

    private const USED_ENV_KEYS = [];


    public function __construct(string $name = null)
    {
        $this->name = $name ?? 'back';
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

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}