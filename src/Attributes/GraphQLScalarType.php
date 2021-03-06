<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GraphQLScalarType
{
  public function __construct(
    public string $typeName,
    public ?bool $allowsNull = null,
  ) {
  }
}
