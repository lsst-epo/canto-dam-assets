<?php

namespace lsst\cantodamassets\gql\resolvers;

use craft\base\ElementInterface;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use lsst\cantodamassets\models\CantoFieldData;

class CantoDamAssetResolver extends Resolver
{
    // List of arguments in the order they should be processed, along with the argument transform method
    protected static array $argsList = [
        [
            'args' => ['where'],
            'method' => 'spreadArgs',
        ],
        [
            'args' => ['whereNull', 'whereNotNull'],
            'method' => 'simpleArgs',
        ],
        [
            'args' => ['whereIn', 'whereNotIn', 'whereBetween', 'whereNotBetween'],
            'method' => 'keyArgs',
        ],
        [
            'args' => ['shuffle', 'sortBy', 'sortByDesc', 'reverse'],
            'method' => 'simpleArgs',
        ],
        [
            'args' => ['first', 'last', 'except', 'nth', 'random', 'skip'],
            'method' => 'simpleArgs',
        ],
    ];

    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        /** @var ElementInterface $source */
        $fieldName = $resolveInfo->fieldName;
        /** @var CantoFieldData $cantoFieldData */
        $cantoFieldData = $source->{$fieldName};

        if (empty($cantoFieldData->cantoAssetData)) {
            return [];
        }

        return static::applyArguments($cantoFieldData->cantoAssetData, $arguments);
    }

    protected static function applyArguments(Collection $collection, array $arguments): Collection
    {
        foreach (static::$argsList as $argList) {
            foreach ($argList['args'] as $arg) {
                if (!empty($arguments[$arg])) {
                    $func = $argList['method'];
                    $collection = static::$func($collection, $arguments, $arg);
                }
            }
        }
        if (is_array($collection)) {
            $collection = new Collection([$collection]);
        }

        return $collection;
    }

    protected static function spreadArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg(...$arguments[$arg]);
    }

    protected static function keyArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        $key = array_shift($arguments[$arg]);
        return $collection->$arg($key, $arguments[$arg]);
    }

    protected static function simpleArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg($arguments[$arg]);
    }

}
