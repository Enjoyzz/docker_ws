<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Envs;


use Enjoys\DockerWs\Env;

final class GROUP_NAME extends Env
{
    protected string $name = 'GROUP_NAME';
    protected ?string $default = 'app';
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
