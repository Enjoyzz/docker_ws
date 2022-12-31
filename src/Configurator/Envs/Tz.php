<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Envs;


final class Tz extends EnvAbstract
{
    protected string $name = 'TZ';
    protected ?string $default = 'UTC';
    protected bool $required = false;

    public function getAutocompleter(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

//    public function getValidator(): ?callable
//    {
//        return function ($answer) {
////            if ($this->isRequired()) {
////                $answer = parent::getValidator()($answer);
////            }
//            if ($answer === null) {
//                return null;
//            }
//            if (!is_string($answer) || !in_array($answer, $this->getAutocompleter(), true)) {
//                throw new \RuntimeException(
//                    'Wrong Timezone'
//                );
//            }
//            return $answer;
//        };
//    }
}
