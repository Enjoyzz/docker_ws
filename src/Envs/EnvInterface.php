<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;


interface EnvInterface
{
    public function getName(): string;

    public function getDefault(): ?string;

    public function getQuestionString(): string;

    public function getValidator(): ?callable;

    public function getNormalizer(): callable;

    public function getAutocompleter(): iterable|callable|null;

    public function isRequired(): bool;
}
