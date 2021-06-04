# ObjectToGraphQL

[![Latest Version on Packagist](https://img.shields.io/packagist/v/palshin/object_to_graphql.svg?style=flat-square)](https://packagist.org/packages/palshin/object_to_graphql)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/palshin/object_to_graphql/run-tests?label=tests)](https://github.com/palshin/object_to_graphql/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/palshin/object_to_graphql/Check%20&%20fix%20styling?label=code%20style)](https://github.com/palshin/object_to_graphql/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/palshin/object_to_graphql.svg?style=flat-square)](https://packagist.org/packages/palshin/object_to_graphql)

I noticed that in my own code (I use [Lighthouse PHP](https://github.com/nuwave/lighthouse) for implementation GraphQL API) quite often I first describe the input type in the schema, then I describe the object for this type (DTO). It creates a sense of repetition and makes type system maintenance more difficult, so I decided to add the ability to programmatically generate types for the schema by its type declaration in code. So in my project I expanded [this recommendation](https://lighthouse-php.com/5/digging-deeper/adding-types-programmatically.html#native-php-types) for third case: DTO for custom resolvers.

## Installation

You can install the package via composer:

```bash
composer require epalshin/object-to-graphql
```

## Usage

```php

use GraphQL\Type\Definition\ObjectType;
use Palshin\ObjectToGraphQL\ObjectToGraphQL;
use Palshin\ObjectToGraphQL\Attributes\GraphQLArrayType;
use Palshin\ObjectToGraphQL\Attributes\GraphQLObjectType;

#[GraphQLObjectType(typeCategory: ObjectToGraphQL::TYPE_CATEGORY_INPUT)]
class ProductCreateDTO
{
  public string $name;

  public string $description;

  public float $price;
  
  public bool $isPublic;

  #[GraphQLArrayType(ObjectToGraphQL::STRING, allowsNull: false)]
  public array $photoUrls;

  public ?ProductCategoryCreateDTO $category;
}

class ProductCategoryCreateDTO
{
  public string $name;
  
  public string $description;
  
  public int $sortOrder;
}
$objectToGraphQL = new ObjectToGraphQL();
[ $productCreateDto, $productCategoryCreateDto ] = $objectToGraphQL->getObjectTypes(ProductCreateDTO::class);

// and now you can register $objectType in your schema
$mutationType = new ObjectType([
  'name' => 'Mutation',
  'fields' => [
    'productCreate' => [
      'type' => $product,
      'args' => [
        'input' => $productCreateDto,
      ],
      'resolve' => function($rootValue, $args) {
        // TODO
      }
    ]
  ]
]);

```

The resulting GraphQL schema will be:

```graphql
input ProductCreateDTOInput {
  name: String!
  description: String!
  price: Float!
  isPublic: Boolean!
  photoUrls: [String!]!
  category: ProductCategoryCreateDTOInput
}
input ProductCategoryCreateDTOInput {
  name: String!
  description: String!
  sortOrder: Int!
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Eugene Palshin](https://github.com/palshin)
- [All Contributors](../../contributors)

## TODO
- [x] Add cyclic addition of types to schema
- [x] Add processing for union types
- [x] Add processing for type category and suffixes parameters passing through attribute
- [x] Add strict mode for early error detection
- [x] Add custom exceptions
- [ ] Write more tests

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
