<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


use Enjoys\DockerWs\Configurator\EnvInterface;

abstract class EnvAbstract implements EnvInterface
{
    protected string $name = '';
    protected ?string $default = null;
    protected bool $required = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getQuestionString(): string
    {
        if ($this->getDefault() === null) {
            return sprintf(
                '  Enter <info>%s</info> value:',
                $this->getName()
            );
        }

        return sprintf(
            '  Enter <info>%s</info> value [<comment>%s</comment>]:',
            $this->getName(),
            $this->getDefault()
        );
    }

    public function getValidator(): ?callable
    {
        if ($this->isRequired() && $this->getDefault() === null) {
            return function ($answer) {
                if ($answer === null) {
                    throw new \RuntimeException(
                        'Эта переменная обязательна и не должна быть пустой строкой'
                    );
                }
                return $answer;
            };
        }
        return null;
    }

    public function getNormalizer(): callable
    {
        return function ($answer) {
            return $answer;
        };
    }

    public function getAutocompleter(): iterable|callable|null
    {
        return null;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
