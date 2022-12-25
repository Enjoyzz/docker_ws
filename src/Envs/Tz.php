<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;



final class Tz extends EnvAbstract
{
    protected string $name = 'TZ';
    protected ?string $default = 'UTC';
    protected bool $required = false;

//    public function getValidator(): ?callable
//    {
//        return function ($answer){
//            if ($this->isRequired()){
//                $answer = parent::getValidator()($answer);
//            }
//            //....
//        };
//    }

    public function autocomplete(): array
    {
        return \DateTimeZone::listIdentifiers();
    }
}
