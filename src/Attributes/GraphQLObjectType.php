<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class GraphQLObjectType
{
  public function __construct(
    public ?string $name,
  ) {
  }
}
