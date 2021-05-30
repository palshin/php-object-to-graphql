<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL;

abstract class ObjectToGraphQLHelper
{
  /**
   * Returns type name of $objectOrClass for GraphQL schema definition.
   *
   * @param string|object $className
   * @param null|string $name
   * @return string
   */
  public static function getTypeName(string | object $objectOrClass, ?string $name = null): string
  {
    if ($name) {
      return $name;
    }

    $className = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

    return basename(str_replace('\\', '/', $className));
  }
}
