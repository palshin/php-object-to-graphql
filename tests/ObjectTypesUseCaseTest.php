<?php

namespace Palshin\ObjectToGraphQL\Tests;

use GraphQL\Type\Definition\InputObjectType;
use Palshin\ObjectToGraphQL\ObjectToGraphQL;
use Palshin\ObjectToGraphQL\Tests\TestClasses\User;
use PHPUnit\Framework\TestCase;

class ObjectTypesUseCaseTest extends TestCase
{
  /**
   * @test
   */
  public function type_with_object_can_be_represented(): InputObjectType
  {
    $objectToGraphQL = new ObjectToGraphQL();
    $objectType = array_values($objectToGraphQL->getObjectTypes(User::class))[0];

    $this->assertSame('UserInput', $objectType->name);

    return $objectType;
  }
}
