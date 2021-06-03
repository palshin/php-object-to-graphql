<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GraphQLArrayType
{
  private GraphQLScalarType $scalarType;

  public function __construct(
    public string $typeName,
    public ?bool $allowsNull = null,
    bool $itemsAllowNull = true,
  ) {
    $this->scalarType = new GraphQLScalarType($typeName, $itemsAllowNull);
  }

  public function getScalarType(): GraphQLScalarType
  {
    return $this->scalarType;
  }
}
