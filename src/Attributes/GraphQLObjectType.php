<?php

declare(strict_types=1);

namespace Palshin\ObjectToGraphQL\Attributes;

use Attribute;
use Exception;
use Palshin\ObjectToGraphQL\HasObjectToGraphQLConstants;

#[Attribute(Attribute::TARGET_CLASS)]
class GraphQLObjectType
{
  /**
   * @param string|null $name
   * @param string|null $typeCategory
   * @throws Exception
   */
  public function __construct(
    public ?string $name = null,
    public ?string $typeCategory = null,
  ) {
    $isAllowedTypeCategory = in_array(
      $this->typeCategory,
      [HasObjectToGraphQLConstants::TYPE_CATEGORY_INPUT, HasObjectToGraphQLConstants::TYPE_CATEGORY_OUTPUT]
    );
    if (! $isAllowedTypeCategory) {
      throw new Exception("Undefined type category for GraphQLObjectType attribute: {$this->typeCategory}");
    }
  }
}
