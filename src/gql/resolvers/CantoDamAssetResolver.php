<?php

namespace lsst\cantodamassets\gql\resolvers;

use craft\base\ElementInterface;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;
use lsst\cantodamassets\lib\laravel\Collection;
use lsst\cantodamassets\models\CantoFieldData;

class CantoDamAssetResolver extends Resolver
{
    // List of arguments in the order they should be processed, along with the argument transform method
    protected static array $argsList = [
        [
            'args' => ['whereContainsIn'],
            'method' => 'whereContainsInArgs',
        ],
        [
            'args' => ['where'],
            'method' => 'whereArgs',
        ],
        [
            'args' => ['whereNull', 'whereNotNull'],
            'method' => 'simpleArgs',
        ],
        [
            'args' => ['whereIn', 'whereNotIn', 'whereBetween', 'whereNotBetween'],
            'method' => 'whereArrayArgs',
        ],
        [
            'args' => ['shuffle', 'reverse'],
            'method' => 'simpleArgs',
        ],
        [
            'args' => ['sortBy', 'sortByDesc'],
            'method' => 'sortArgs',
        ],
        [
            'args' => ['except', 'nth', 'random', 'skip'],
            'method' => 'simpleArgs',
        ],
        [
            'args' => ['forPage'],
            'method' => 'forPageArgs',
        ],
        [
            'args' => ['first', 'last'],
            'method' => 'noArgs',
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

        return $collection;
    }

    protected static function forPageArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg(
            $arguments[$arg]['page'] ?? 1,
            $arguments[$arg]['items'] ?? 10,
        );
    }

    protected static function whereArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg(
            $arguments[$arg]['key'] ?? null,
            $arguments[$arg]['operator'] ?? null,
            $arguments[$arg]['value'] ?? null
        );
    }

    protected static function sortArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        $resolvedArg = count($arguments[$arg]) === 1 ? reset($arguments[$arg]) : $arguments[$arg];
        return $collection->$arg($resolvedArg);
    }

    protected static function whereContainsInArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg(
            $arguments[$arg]['keys'] ?? null,
            $arguments[$arg]['value'] ?? null
        );
    }

    protected static function whereArrayArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg(
            $arguments[$arg]['key'] ?? null,
            $arguments[$arg]['values'] ?? null
        );
    }

    protected static function simpleArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return $collection->$arg($arguments[$arg]);
    }

    protected static function noArgs(Collection $collection, array $arguments, string $arg): Collection
    {
        return new Collection([$collection->$arg(null)]);
    }
}
