<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

use Closure;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLObjectType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLScalarType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLUnionType;
use Palshin\ObjectToGraphQL\Exceptions\NoFoundGraphQLTypeForPropertyException;
use Palshin\ObjectToGraphQL\Exceptions\NoFoundScalarClassException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class ObjectToGraphQL implements HasObjectToGraphQLConstants
{
  /**
   * @var array<string, ObjectType|InputObjectType|UnionType|null>
   */
  protected array $typeInstances = [];

  public function __construct(
    protected string $typeCategory = self::TYPE_CATEGORY_INPUT,
    protected string $inputSuffix = 'Input',
    protected string $outputSuffix = '',
    protected $strict = false,
  ) {
  }

  /**
   * @psalm-suppress InvalidReturnType, InvalidReturnStatement
   * @param class-string|object $objectOrClass
   * @psalm-return array<string, ObjectType|InputObjectType|UnionType>
   * @throws ReflectionException
   * @throws Exception
   */
  public function getObjectTypes(string | object $objectOrClass): array
  {
    $this->getObjectType($objectOrClass);

    return $this->typeInstances;
  }

  /**
   * Method returns [ObjectType] for register in GraphQL schema to represent $objectOrClass object.
   * @psalm-suppress InvalidReturnType
   * @param class-string|object $objectOrClass
   * @return ObjectType|InputObjectType|Closure
   * @throws ReflectionException
   * @throws Exception
   */
  protected function getObjectType(string | object $objectOrClass): ObjectType | InputObjectType | Closure
  {
    $reflection = new ReflectionClass($objectOrClass);
    $objectType = $reflection->getAttributes(GraphQLObjectType::class)[0] ?? null;
    /**
     * @var GraphQLObjectType|null $objectTypeInstance
     */
    $objectTypeInstance = $objectType?->newInstance();
    $name = $objectTypeInstance?->name;
    $typeCategory = $objectTypeInstance?->typeCategory ?? $this->typeCategory;
    $typeName = $this->getTypeName($objectOrClass, $name, $typeCategory);
    // for cyclic dependencies use deferred field definition, by returning closure
    if (array_key_exists($typeName, $this->typeInstances)) {
      /*
       * @psalm-suppress InvalidReturnStatement
       */
      return $this->typeInstances[$typeName] ?? fn () => $this->typeInstances[$typeName];
    }
    $this->typeInstances[$typeName] = null;
    /**
     * @var array<string,Type|null>
     */
    $fields = [];
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    foreach ($properties as $property) {
      try {
        $typeField = $this->getTypeField($property);
      } catch (NoFoundGraphQLTypeForPropertyException $exception) {
        throw new NoFoundGraphQLTypeForPropertyException(
          $reflection->getName().'->'.$exception->propertyPath
        );
      }
      if ($typeField) {
        $fields[$property->getName()] = $typeField;
      }
    }
    $config = [
      'name' => $typeName,
      'fields' => $fields,
    ];
    $ObjectTypeClass = $typeCategory === self::TYPE_CATEGORY_INPUT
      ? InputObjectType::class
      : ObjectType::class;

    $typeInstance = new $ObjectTypeClass($config);
    $this->typeInstances[$typeName] = $typeInstance;

    return $typeInstance;
  }

  /**
   * @param class-string|object $objectOrClass
   * @param null|string $name
   * @param string $typeCategory
   * @return string
   */
  protected function getTypeName(
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
   * @param ReflectionProperty $property
   * @return Type|Closure|null
   * @throws NoFoundGraphQLTypeForPropertyException
   * @throws ReflectionException
   * @throws NoFoundScalarClassException
   */
  protected function getTypeField(ReflectionProperty $property): Type | Closure | null
  {
    // first case: we have attribute with property definition, so it has highest priority
    $scalarTypeAttribute = $property->getAttributes(GraphQLScalarType::class)[0] ?? null;
    if ($scalarTypeAttribute) {
      /**
       * @var GraphQLScalarType $scalarType
       */
      $scalarType = $scalarTypeAttribute->newInstance();
      $typeInstance = $this->getTypeInstanceForScalar($scalarType->typeName);

      return ObjectToGraphQLHelper::wrapNull($typeInstance, $property, $scalarType);
    }

    $arrayTypeAttribute = $property->getAttributes(GraphQLArrayType::class)[0] ?? null;
    if ($arrayTypeAttribute) {
      /**
       * @var GraphQLArrayType $arrayType
       */
      $arrayType = $arrayTypeAttribute->newInstance();
      $typeInstance = $this->getTypeInstanceForNamedType($arrayType->getScalarType()->typeName);
      $type = $arrayType->getScalarType()->allowsNull ? $typeInstance : Type::nonNull($typeInstance);

      return ObjectToGraphQLHelper::wrapNull(Type::listOf($type), $property, $arrayType);
    }

    $type = $property->getType();

    // second case: we doesn't have attribute, so we have single type declaration
    if ($type instanceof ReflectionNamedType) {
      $typeInstance = $this->getTypeInstanceForNamedType($type->getName());

      return ObjectToGraphQLHelper::wrapNull($typeInstance, $property);
    }

    // third case: we have union type declaration
    if ($type instanceof ReflectionUnionType) {
      $unionTypeAttribute = $property->getAttributes(GraphQLUnionType::class)[0] ?? null;
      if ($unionTypeAttribute) {
        /**
         * @var GraphQLUnionType $unionType
         */
        $unionType = $unionTypeAttribute->newInstance();
        $typeInstance = new UnionType([
          'name' => $unionType->name,
          'types' => array_map(
            fn (ReflectionNamedType $type) => $this->getTypeInstanceForNamedType($type->getName()),
            ObjectToGraphQLHelper::filterNullType($type->getTypes()),
          ),
        ]);
        $this->typeInstances[$unionType->name] = $typeInstance;

        return ObjectToGraphQLHelper::hasNullTypeInUnion($type->getTypes())
          ? $typeInstance
          : Type::nonNull($typeInstance);
      }
    }
    if ($this->strict) {
      throw new NoFoundGraphQLTypeForPropertyException($property->getName());
    }

    return null;
  }

  /**
   * @psalm-suppress LessSpecificReturnStatement
   * @param string|class-string<ScalarType>|class-string<ScalarType&HasInstance> $ClassName
   * @return ScalarType | ObjectType
   * @throws NoFoundScalarClassException
   */
  protected function getTypeInstanceForScalar(string $ClassName): ScalarType | ObjectType
  {
    $typeInstance = ObjectToGraphQLHelper::getScalarTypeInstanceByName($ClassName);
    if ($typeInstance) {
      return $typeInstance;
    }
    if (class_exists($ClassName) && in_array(HasInstance::class, class_implements($ClassName))) {
      /*
       * @psalm-var class-string<HasInstance> $ClassName
       */
      return $ClassName::getInstance();
    }

    throw new NoFoundScalarClassException($ClassName);
  }

  /**
   * @param string|class-string $typeName
   * @return InputObjectType|ObjectType|ScalarType|Closure
   * @throws ReflectionException
   * @throws Exception
   */
  protected function getTypeInstanceForNamedType(string $typeName): InputObjectType | ObjectType | ScalarType | Closure
  {
    return ObjectToGraphQLHelper::getScalarTypeInstanceByName($typeName) ?? $this->getObjectType($typeName);
  }
}
