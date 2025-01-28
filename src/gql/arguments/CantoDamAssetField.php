<?php

namespace lsst\cantodamassets\gql\arguments;

use Craft;
use craft\gql\base\Arguments;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class CantoDamAssetField extends Arguments
{
    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        $sortFlagsType = new EnumType([
            'name' => 'PHPSortFlags',
            'description' => 'PHP sort flags that determine how items are compared. Defaults to SORT_NATURAL_CASE - https://www.php.net/manual/en/function.sort.php',
            'values' => [
                'SORT_REGULAR' => [
                    'value' => SORT_REGULAR,
                    'description' => 'compare items normally; the details are described in the comparison operators section',
                ],
                'SORT_NUMERIC' => [
                    'value' => SORT_NUMERIC,
                    'description' => 'compare items numerically.',
                ],
                'SORT_STRING' => [
                    'value' => SORT_STRING,
                    'description' => 'compare items as strings',
                ],
                'SORT_STRING_CASE' => [
                    'value' => SORT_STRING | SORT_FLAG_CASE,
                    'description' => 'compare items as case insensitive strings',
                ],
                'SORT_LOCALE_STRING' => [
                    'value' => SORT_LOCALE_STRING,
                    'description' => 'compare items as strings, based on the current locale. It uses the locale, which can be changed using setlocale()',
                ],
                'SORT_NATURAL' => [
                    'value' => SORT_NATURAL,
                    'description' => 'compare items as strings using "natural ordering" like natsort()',
                ],
                'SORT_NATURAL_CASE' => [
                    'value' => SORT_NATURAL | SORT_FLAG_CASE,
                    'description' => 'compare items as case insensitive strings using "natural ordering" like natsort()',
                ],
            ],
        ]);
        return Craft::$app->getGql()->prepareFieldDefinitions([
            'except' => [
                'name' => 'except',
                'description' => 'Get all items except for those with the specified indexes.',
                'type' => Type::listOf(Type::int()),
            ],
            'nth' => [
                'name' => 'nth',
                'description' => 'Return a collection consisting of every n-th element.',
                'type' => Type::int(),
            ],
            'last' => [
                'name' => 'last',
                'description' => 'Get the last item from the collection.',
                'type' => Type::boolean(),
            ],
            'random' => [
                'name' => 'random',
                'description' => 'Get the specified number of items randomly from the collection.',
                'type' => Type::int(),
            ],
            'reverse' => [
                'name' => 'reverse',
                'description' => 'Reverse the list',
                'type' => Type::boolean(),
            ],
            'first' => [
                'name' => 'first',
                'description' => 'Get the first item from the collection.',
                'type' => Type::boolean(),
            ],
            'shuffle' => [
                'name' => 'shuffle',
                'description' => 'Shuffle the items in the collection, using the value as a random number seed.',
                'type' => Type::int(),
            ],
            'skip' => [
                'name' => 'skip',
                'description' => 'Skip the first N items.',
                'type' => Type::int(),
            ],
            'sortBy' => [
                'name' => 'sortBy',
                'description' => 'Sort the collection using the sort string(s).',
                'type' => new InputObjectType([
                    'name' => 'SortByInput',
                    'fields' => [
                        'field' => [
                            'type' => Type::string(),
                            'description' => 'The field to sort by. You can use the `field.subField` syntax for nested fields and provide multiple sort commands as a list of strings.',
                        ],
                        'flags' => [
                            'type' => $sortFlagsType,
                            'description' => 'PHP sort flags that determine how items are compared. Defaults to SORT_NATURAL - https://www.php.net/manual/en/function.sort.php',
                        ],
                    ],
                ]),
            ],
            'sortByDesc' => [
                'name' => 'sortByDesc',
                'description' => 'Sort the collection using the sort string(s) in a descending order.',
                'type' => new InputObjectType([
                    'name' => 'SortByDescInput',
                    'fields' => [
                        'field' => [
                            'type' => Type::string(),
                            'description' => 'The field to sort by. You can use the `field.subField` syntax for nested fields and provide multiple sort commands as a list of strings.',
                        ],
                        'flags' => [
                            'type' => $sortFlagsType,
                            'description' => 'PHP sort flags that determine how items are compared. Defaults to SORT_NATURAL - https://www.php.net/manual/en/function.sort.php',
                        ],
                    ],
                ]),
            ],
            'forPage' => [
                'name' => 'forPage',
                'description' => 'Paginate the items by page number and items per page. (See https://laravel.com/docs/10.x/collections#method-forpage).',
                'type' => new InputObjectType([
                    'name' => 'ForPageInput',
                    'fields' => [
                        'page' => [
                            'type' => Type::int(),
                            'description' => 'The page number',
                        ],
                        'items' => [
                            'type' => Type::int(),
                            'description' => 'The number of items per page',
                        ],
                    ],
                ]),
            ],
            'whereContainsIn' => [
                'name' => 'whereContainsIn',
                'description' => 'Look across the given key-values and return fuzzy match on a single search term',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereContainsInFilterInput',
                    'fields' => [
                        'keys' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The keys to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'value' => [
                            'type' => Type::string(),
                            'description' => 'The value that should be fuzzy matched in the key-values',
                        ],
                    ],
                ])),
            ],
            'where' => [
                'name' => 'where',
                'description' => 'Get all items by the given key value pair, using the optional operator for comparison. (See https://laravel.com/docs/10.x/collections#method-where).',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'value' => [
                            'type' => Type::string(),
                            'description' => 'The value to match when searching',
                        ],
                        'operator' => [
                            'type' => Type::string(),
                            'description' => 'The comparison operator to use, e.g.: `=`, `>`, `<=`, etc. The default is `=`',
                        ],
                    ],
                ])),
            ],
            'whereNull' => [
                'name' => 'whereNull',
                'description' => 'Return items from the collection where the given key is null. You can use the `field.subField` syntax for nested fields.',
                'type' => Type::string(),
            ],
            'whereNotNull' => [
                'name' => 'whereNotNull',
                'description' => 'Return items from the collection where the given key is not null. You can use the `field.subField` syntax for nested fields.',
                'type' => Type::string(),
            ],
            'whereIn' => [
                'name' => 'whereIn',
                'description' => 'Filter items such that the value of the given key is in the array of values provided.  (See https://laravel.com/docs/10.x/collections#method-wherein).',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereInFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values that should be in the key',
                        ],
                    ],
                ])),
            ],
            'whereNotIn' => [
                'name' => 'whereNotIn',
                'description' => 'Filter items by the given key value pair, making sure the value is NOT in the array. (See https://laravel.com/docs/10.x/collections#method-wherenotin).',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereNotInFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The the values that should not be in the key',
                        ],
                    ],
                ])),
            ],
            'whereBetween' => [
                'name' => 'whereBetween',
                'description' => 'Filter items such that the value of the given key is between the given values. (See https://laravel.com/docs/10.x/collections#method-wherebetween).',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereBetweenFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values that the key should be between',
                        ],
                    ],
                ])),
            ],
            'whereNotBetween' => [
                'name' => 'whereNotBetween',
                'description' => 'Filter items such that the value of the given key is not between the given values.  (See https://laravel.com/docs/10.x/collections#method-wherenotbetween).',
                'type' => Type::listOf(new InputObjectType([
                    'name' => 'WhereNotBetweenFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields',
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values the key should not be between',
                        ],
                    ],
                ])),
            ],
        ], 'CantoDamAssetArgumentsType');
    }
}
