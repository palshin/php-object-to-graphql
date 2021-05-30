<?php

namespace Palshin\ObjectToGraphQL\Tests;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Palshin\ObjectToGraphQL\ObjectToGraphQL;
use Palshin\ObjectToGraphQL\Tests\TestClasses\User;
use PHPUnit\Framework\TestCase;

class ObjectTypesUseCaseTest extends TestCase
{
  /**
   * @var array<'UserInput'|'PostInput', InputObjectType>
   */
  private array $objectTypes = [];

  public function setUp(): void
  {
    parent::setUp();

    $objectToGraphQL = new ObjectToGraphQL();
    $this->objectTypes = $objectToGraphQL->getObjectTypes(User::class);
  }

  /**
   * @test
   */
  public function user_type_was_created(): void
  {
    $this->assertSame('UserInput', $this->objectTypes['UserInput']->name);
  }

  /**
   * @test
   */
  public function post_type_was_created(): void
  {
    $this->assertSame('PostInput', $this->objectTypes['PostInput']->name);
  }

  /**
   * @test
   */
  public function type_definitions_match(): void
  {
    $postInput = new InputObjectType([
      'name' => 'PostInput',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'title' => Type::nonNull(Type::string()),
        'content' => type::nonNull(Type::string()),
      ],
    ]);

    $userInput = new InputObjectType([
      'name' => 'UserInput',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'name' => Type::nonNull(Type::string()),
        'posts' => Type::nonNull(Type::listOf(Type::nonNull($postInput))),
        'lastPost' => $postInput,
      ],
    ]);

    $this->assertEquals($userInput, $this->objectTypes['UserInput']);
  }
}
