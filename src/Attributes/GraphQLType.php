<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use GraphQL\Type\Definition\ScalarType;

abstract class GraphQLType
{
  /**
   * @param string|class-string<ScalarType> $typeClass
   * @param null|bool $allowsNull
   * @return void
   */
  public function __construct(
    public string $typeClass,
    public ?bool $allowsNull = null,
  ) {
  }
}
