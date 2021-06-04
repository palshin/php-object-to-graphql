<?php

namespace Palshin\ObjectToGraphQL\Exceptions;

use Exception;

class NoFoundGraphQLTypeForPropertyException extends Exception
{
  public function __construct(public string $propertyPath = '')
  {
    parent::__construct("Could not find the matching GraphQL type for property: $propertyPath");
  }
}
