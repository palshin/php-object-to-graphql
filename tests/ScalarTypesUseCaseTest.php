<?php

namespace Palshin\ObjectToGraphQL\Tests;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Palshin\ObjectToGraphQL\ObjectToGraphQL;
use Palshin\ObjectToGraphQL\Tests\TestClasses\ScalarClass;
use PHPUnit\Framework\TestCase;

class ScalarTypesUseCaseTest extends TestCase
{
  /** @test */
  public function object_type_can_be_created(): InputObjectType
  {
    $objectToGraphQL = new ObjectToGraphQL();
    /**
     * @var InputObjectType $objectType
     */
    $objectType = array_values($objectToGraphQL->getObjectTypes(ScalarClass::class))[0];
    $this->assertSame('ScalarClassInput', $objectType->name);

    return $objectType;
  }

  /**
   * @test
   * @depends object_type_can_be_created
   */
  public function int_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $intValFieldDefinition = $objectType->getField('intVal');
    $this->assertEquals(Type::nonNull(Type::int()), $intValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends int_property_can_be_represented
   */
  public function null_int_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullIntValFieldDefinition = $objectType->getField('nullIntVal');
    $this->assertEquals(Type::int(), $nullIntValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends null_int_property_can_be_represented
   */
  public function attribute_int_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullIntValFieldDefinition = $objectType->getField('attributeIntVal');
    $this->assertEquals(Type::int(), $nullIntValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends attribute_int_property_can_be_represented
   */
  public function float_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $floatValFieldDefinition = $objectType->getField('floatVal');
    $this->assertEquals(Type::nonNull(Type::float()), $floatValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends float_property_can_be_represented
   */
  public function null_float_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullFloatValFieldDefinition = $objectType->getField('nullFloatVal');
    $this->assertEquals(Type::float(), $nullFloatValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends null_float_property_can_be_represented
   */
  public function attribute_float_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullFloatValFieldDefinition = $objectType->getField('attributeFloatVal');
    $this->assertEquals(Type::float(), $nullFloatValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends attribute_float_property_can_be_represented
   */
  public function string_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $stringFieldDefinition = $objectType->getField('stringVal');
    $this->assertEquals(Type::nonNull(Type::string()), $stringFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends string_property_can_be_represented
   */
  public function null_string_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullStringFieldDefinition = $objectType->getField('nullStringVal');
    $this->assertEquals(Type::string(), $nullStringFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends null_string_property_can_be_represented
   */
  public function attribute_string_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullStringFieldDefinition = $objectType->getField('attributeStringVal');
    $this->assertEquals(Type::string(), $nullStringFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends attribute_string_property_can_be_represented
   */
  public function bool_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $boolValFieldDefinition = $objectType->getField('boolVal');
    $this->assertEquals(Type::nonNull(Type::boolean()), $boolValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends bool_property_can_be_represented
   */
  public function null_bool_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullBoolValFieldDefinition = $objectType->getField('nullBoolVal');
    $this->assertEquals(Type::boolean(), $nullBoolValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends null_bool_property_can_be_represented
   */
  public function attribute_bool_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $nullBoolValFieldDefinition = $objectType->getField('attributeBoolVal');
    $this->assertEquals(Type::boolean(), $nullBoolValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends attribute_bool_property_can_be_represented
   */
  public function id_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $idValFieldDefinition = $objectType->getField('idVal');
    $this->assertEquals(Type::nonNull(Type::id()), $idValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends id_property_can_be_represented
   */
  public function null_id_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $idValFieldDefinition = $objectType->getField('nullIdVal');
    $this->assertEquals(Type::id(), $idValFieldDefinition->getType());

    return $objectType;
  }

  /**
   * @test
   * @depends null_id_property_can_be_represented
   */
  public function int_array_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $intArrayFieldDefinition = $objectType->getField('intArray');
    $this->assertEquals(
      Type::nonNull(
        Type::listOf(
          Type::nonNull(
            Type::int()
          )
        )
      ),
      $intArrayFieldDefinition->getType()
    );

    return $objectType;
  }

  /**
   * @test
   * @depends int_array_property_can_be_represented
   */
  public function float_array_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $floatArrayFieldDefinition = $objectType->getField('floatArray');
    $this->assertEquals(
      Type::nonNull(
        Type::listOf(
          Type::nonNull(
            Type::float()
          )
        )
      ),
      $floatArrayFieldDefinition->getType()
    );

    return $objectType;
  }

  /**
   * @test
   * @depends float_array_property_can_be_represented
   */
  public function string_array_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $stringArrayFieldDefinition = $objectType->getField('stringArray');
    $this->assertEquals(
      Type::nonNull(
        Type::listOf(
          Type::string()
        )
      ),
      $stringArrayFieldDefinition->getType()
    );

    return $objectType;
  }

  /**
   * @test
   * @depends string_array_property_can_be_represented
   */
  public function bool_array_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $boolArrayFieldDefinition = $objectType->getField('boolArray');
    $this->assertEquals(
      Type::nonNull(
        Type::listOf(
          Type::nonNull(
            Type::boolean()
          )
        )
      ),
      $boolArrayFieldDefinition->getType()
    );

    return $objectType;
  }

  /**
   * @test
   * @depends bool_array_property_can_be_represented
   */
  public function id_array_property_can_be_represented(InputObjectType $objectType): InputObjectType
  {
    $idArrayFieldDefinition = $objectType->getField('idArray');
    $this->assertEquals(
      Type::listOf(
        Type::id()
      ),
      $idArrayFieldDefinition->getType()
    );

    return $objectType;
  }
}
