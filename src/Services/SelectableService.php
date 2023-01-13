<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


interface SelectableService
{
    public function getSelectedService(): ?ServiceInterface;
}