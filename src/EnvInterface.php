<?php

declare(strict_types=1);

namespace Enjoys\DockerWs;

interface EnvInterface
{
    public function getAutocompleter(): iterable|callable|null;

    public function getValidator(): ?callable;

    public function getNormalizer(): callable;

    public function getName(): string;

    public function getQuestionString(): string;

    public function getDefault(): ?string;
}