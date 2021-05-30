<?php

namespace Palshin\ObjectToGraphQL\Tests\TestClasses;

use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;

class User
{
  public int $id;

  public string $name;

  #[GraphQLArrayType(Post::class, itemsAllowNull: false)]
  public array $posts;

  public ?Post $lastPost;
}
