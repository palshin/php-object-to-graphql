<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

use GraphQL\Type\Definition\ScalarType;

interface HasInstance
{
  public static function getInstance(): ScalarType;
}
