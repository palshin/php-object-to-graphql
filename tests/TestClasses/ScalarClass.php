<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Tests\TestClasses;

use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLScalarType;
use Palshin\ObjectToGraphQL\ObjectToGraphQL;

class ScalarClass
{
  public int $intVal;

  public ?int $nullIntVal;

  #[GraphQLScalarType(ObjectToGraphQL::INT)]
  public $attributeIntVal;

  public string $stringVal;

  public ?string $nullStringVal;

  #[GraphQLScalarType(ObjectToGraphQL::STRING)]
  public $attributeStringVal;

  public float $floatVal;

  public ?float $nullFloatVal;

  #[GraphQLScalarType(ObjectToGraphQL::FLOAT)]
  public $attributeFloatVal;

  public bool $boolVal;

  public ?bool $nullBoolVal;

  #[GraphQLScalarType(ObjectToGraphQL::BOOLEAN)]
  public $attributeBoolVal;

  #[GraphQLScalarType(ObjectToGraphQL::ID)]
  public string $idVal;

  #[GraphQLScalarType(ObjectToGraphQL::ID)]
  public ?string $nullIdVal;

  #[GraphQLArrayType(ObjectToGraphQL::INT, itemsAllowNull: false)]
  public array $intArray;

  #[GraphQLArrayType(ObjectToGraphQL::FLOAT, itemsAllowNull: false)]
  public array $floatArray;

  #[GraphQLArrayType(ObjectToGraphQL::STRING, itemsAllowNull: true)]
  public array $stringArray;

  #[GraphQLArrayType(ObjectToGraphQL::BOOLEAN, itemsAllowNull: false)]
  public array $boolArray;

  #[GraphQLArrayType(ObjectToGraphQL::ID, itemsAllowNull: true)]
  public ?array $idArray;
}
