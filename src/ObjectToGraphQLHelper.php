<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

use Closure;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLScalarType;
use ReflectionNamedType;
use ReflectionProperty;

abstract class ObjectToGraphQLHelper
{
  /**
   * @param ReflectionNamedType[] $reflectionTypes
   * @return bool
   */
  public static function hasNullTypeInUnion(array $reflectionTypes): bool
  {
    return count(
      array_filter(
        $reflectionTypes,
        fn (ReflectionNamedType $type): bool => $type->allowsNull()
      )
    ) !== 0;
  }

  /**
   * @param ReflectionNamedType[] $reflectionTypes
   * @return ReflectionNamedType[]
   */
  public static function filterNullType(array $reflectionTypes): array
  {
    return array_filter(
      $reflectionTypes,
      fn (ReflectionNamedType $type): bool => ! $type->allowsNull()
    );
  }

  public static function getScalarTypeInstanceByName(string $scalarClassName): ?ScalarType
  {
    return match ($scalarClassName) {
      StringType::class, HasObjectToGraphQLConstants::STRING, 'string' => Type::string(),
      IntType::class, HasObjectToGraphQLConstants::INT, 'int' => Type::int(),
      FloatType::class, HasObjectToGraphQLConstants::FLOAT, 'float' => Type::float(),
      BooleanType::class, HasObjectToGraphQLConstants::BOOLEAN, 'boolean' => Type::boolean(),
      IDType::class, HasObjectToGraphQLConstants::ID => Type::id(),
      default => null,
    };
  }

  /**
   * @psalm-param (NullableType&Type)|Closure $type
   * @param Type|Closure $type
   * @param ReflectionProperty $property
   * @param GraphQLScalarType|GraphQLArrayType|null $graphQLType
   * @return Type
   */
  public static function wrapNull(
    Type | Closure $type,
    ReflectionProperty $property,
    GraphQLScalarType | GraphQLArrayType | null $graphQLType = null
  ): Type {
    $allowsNull = $graphQLType?->allowsNull === true || ($property->getType()?->allowsNull() ?? true);

    return $allowsNull ? $type : Type::nonNull($type);
  }
}
