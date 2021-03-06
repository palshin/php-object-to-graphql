<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GraphQLUnionType
{
  public function __construct(
    public string $name,
  ) {
  }
}
