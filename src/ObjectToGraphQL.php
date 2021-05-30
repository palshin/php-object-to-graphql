<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

use Closure;
use Exception;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLObjectType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLScalarType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLType;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class ObjectToGraphQL implements HasObjectToGraphQLConstants
{
  protected const DEFAULT_CONFIG = [
    'typeCategory' => self::TYPE_CATEGORY_INPUT,
    'inputSuffix' => 'Input',
    'outputSuffix' => '',
  ];

  /**
   * @var array<string, ObjectType|InputObjectType|null>
   */
  public array $typeInstances = [];

  /**
   * @var self::TYPE_CATEGORY_INPUT|self::TYPE_CATEGORY_OUTPUT
   */
  public string $typeCategory;

  public string $inputSuffix;

  public string $outputSuffix;

  public function __construct(array $config = [])
  {
    $this->typeCategory = $config['typeCategory'] ?? static::DEFAULT_CONFIG['typeCategory'];
    $this->inputSuffix = $config['inputSuffix'] ?? static::DEFAULT_CONFIG['inputSuffix'];
    $this->outputSuffix = $config['outputSuffix'] ?? static::DEFAULT_CONFIG['outputSuffix'];
  }

  /**
   * @param class-string|object $objectOrClass
   * @return array<string, ObjectType|InputObjectType>
   * @throws ReflectionException
   * @throws Exception
   */
  public function getObjectTypes(string | object $objectOrClass): array
  {
    $this->getObjectType($objectOrClass);

    /**
     * @psalm-var array<string, ObjectType|InputObjectType> $typeInstances
     */
    $typeInstances = $this->typeInstances;

    return $typeInstances;
  }

  /**
   * Method returns [ObjectType] for register in GraphQL schema to represent $objectOrClass object.
   *
   * @param class-string|object $objectOrClass
   * @param string|null $name
   * @return ObjectType|InputObjectType | Closure
   * @throws ReflectionException
   * @throws Exception
   */
  private function getObjectType(string | object $objectOrClass): ObjectType | InputObjectType | Closure
  {
    $reflection = new ReflectionClass($objectOrClass);
    $objectType = $reflection->getAttributes(GraphQLObjectType::class)[0] ?? null;
    $name = $objectType?->newInstance()?->name;
    $typeName = $this->getTypeName($objectOrClass, $name, $this->typeCategory);
    // for cyclic dependecies use deferred field definition, by returning closure
    if (array_key_exists($typeName, $this->typeInstances)) {
      return $this->typeInstances[$typeName] ?? fn () => $this->typeInstances[$typeName];
    }
    $this->typeInstances[$typeName] = null;
    /**
     * @var array<string,Type|null>
     */
    $fields = [];
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    foreach ($properties as $property) {
      $fields[$property->getName()] = $this->getTypeField($property);
    }
    $config = [
      'name' => $typeName,
      'fields' => $fields,
    ];
    $ObjectTypeClass = $this->typeCategory === self::TYPE_CATEGORY_INPUT
      ? InputObjectType::class
      : ObjectType::class;

    $typeInstance = new $ObjectTypeClass($config);
    $this->typeInstances[$typeName] = $typeInstance;

    return $typeInstance;
  }

  /**
   * @param class-string|object $objectOrClass
   * @param null|string $name
   * @param self::TYPE_CATEGORY_INPUT|self::TYPE_CATEGORY_OUTPUT $typeCategory
   * @return string
   */
  private function getTypeName(
    string | object $objectOrClass,
    ?string $name = null,
    string $typeCategory = self::TYPE_CATEGORY_INPUT
  ): string {
    if ($name) {
      return $name;
    }

    $className = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;
    $typeName = basename(str_replace('\\', '/', $className));
    $typeName .= $typeCategory === self::TYPE_CATEGORY_INPUT ? $this->inputSuffix : $this->outputSuffix;

    return $typeName;
  }

  /**
   * @throws Exception
   */
  private function getTypeField(ReflectionProperty $property): Type
  {
    // first case: we have attribute with property definition, so it has highest priority
    $scalarTypeAttribute = $property->getAttributes(GraphQLScalarType::class)[0] ?? null;
    if ($scalarTypeAttribute) {
      $scalarType = $scalarTypeAttribute->newInstance();
      $typeInstance = $this->getTypeInstanceForScalar($scalarType->typeClass);

      return $this->wrapNull($typeInstance, $scalarType, $property);
    }

    $arrayTypeAttribute = $property->getAttributes(GraphQLArrayType::class)[0] ?? null;
    if ($arrayTypeAttribute) {
      $arrayType = $arrayTypeAttribute->newInstance();
      $typeInstance = $this->getTypeInstanceForNamedType($arrayType->getScalarType()->typeClass);
      $type = $arrayType->getScalarType()->allowsNull ? $typeInstance : Type::nonNull($typeInstance);

      return $this->wrapNull(Type::listOf($type), $arrayType, $property);
    }

    $type = $property->getType();

    // second case: we doesn't have attribute, so we have single type declaration
    if ($type instanceof ReflectionNamedType) {
      $typeInstance = $this->getTypeInstanceForNamedType($type->getName());

      return $type->allowsNull() ? $typeInstance : Type::nonNull($typeInstance);
    }

    // third case: we have union type declaration
    if ($type instanceof ReflectionUnionType) {
      var_dump($type->getTypes());
    }
  }

  /**
   * @param (NullableType&Type) $type
   * @param GraphQLType $graphQLType
   * @param ReflectionProperty $property
   * @return Type
   */
  private function wrapNull(Type $type, GraphQLType $graphQLType, ReflectionProperty $property): Type
  {
    $allowsNull = $graphQLType->allowsNull === true || ($property->getType()?->allowsNull() ?? true);

    return $allowsNull ? $type : Type::nonNull($type);
  }

  /**
   * @psalm-suppress LessSpecificReturnStatement
   * @param string|class-string<ScalarType>|class-string<ScalarType&HasInstance> $ClassName
   * @return ScalarType | ObjectType
   * @throws Exception
   */
  private function getTypeInstanceForScalar(string $ClassName): ScalarType | ObjectType
  {
    $typeInstance = $this->getScalarTypeInstanceByName($ClassName);
    if ($typeInstance) {
      return $typeInstance;
    }
    if (class_exists($ClassName) && in_array(HasInstance::class, class_implements($ClassName))) {
      /*
       * @psalm-var class-string<HasInstance> $ClassName
       */
      return $ClassName::getInstance();
    }

    // TODO: add custom exception class
    throw new Exception(
      'Expected builtin scalar type class or custom scalar class that implements '
      .HasInstance::class
      .' but received '.$ClassName
    );
  }

  private function getScalarTypeInstanceByName(string $scalarClassName): ?ScalarType
  {
    return match ($scalarClassName) {
      StringType::class, self::STRING, 'string' => Type::string(),
      IntType::class, self::INT, 'int' => Type::int(),
      FloatType::class, self::FLOAT, 'float' => Type::float(),
      BooleanType::class, self::BOOLEAN, 'boolean' => Type::boolean(),
      IDType::class, self::ID => Type::id(),
      default => null,
    };
  }

  /**
   * @param string|class-string $typeName
   * @return InputObjectType|ObjectType|ScalarType
   * @throws ReflectionException
   * @throws Exception
   */
  private function getTypeInstanceForNamedType(string $typeName): InputObjectType | ObjectType | ScalarType | Closure
  {
    return $this->getScalarTypeInstanceByName($typeName) ?? $this->getObjectType($typeName);
  }
}
