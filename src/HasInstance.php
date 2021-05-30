<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

use GraphQL\Type\Definition\Type;

interface HasInstance
{
  public static function getInstance(): Type;
}
