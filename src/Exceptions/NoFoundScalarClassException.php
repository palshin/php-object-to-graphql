<?php

namespace Palshin\ObjectToGraphQL\Exceptions;

use Exception;
use Palshin\ObjectToGraphQL\HasInstance;

class NoFoundScalarClassException extends Exception
{
  public function __construct(string $ClassName)
  {
    parent::__construct(
      'Expected builtin scalar type class or custom scalar class that implements '
      . HasInstance::class
      . ' but received ' . $ClassName
    );
  }
}
