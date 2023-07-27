<?php

namespace lsst\cantodamassets\gql\resolvers;

use craft\base\ElementInterface;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use lsst\cantodamassets\models\CantoFieldData;

class CantoDamAssetResolver extends Resolver
{
    /**
     * @inheritDoc
     */
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
        // Filter by where args
        $whereArgs = ['where', 'whereNull', 'whereNotNull', 'whereIn', 'whereNotIn', 'whereBetween', 'whereNotBetween'];
        foreach ($whereArgs as $whereArg) {
            if (!empty($arguments[$whereArg])) {
                switch ($whereArg) {
                    case 'where':
                        $collection = $collection->where(...$arguments[$whereArg]);
                        break;
                    case 'whereNull':
                        $collection = $collection->whereNull($arguments[$whereArg]);
                        break;
                    case 'whereNotNull':
                        $collection = $collection->whereNotNull($arguments[$whereArg]);
                        break;
                    case 'whereIn':
                        $key = array_shift($arguments[$whereArg]);
                        $collection = $collection->whereIn($key, $arguments[$whereArg]);
                        break;
                    case 'whereNotIn':
                        $key = array_shift($arguments[$whereArg]);
                        $collection = $collection->whereNotIn($key, $arguments[$whereArg]);
                        break;
                    case 'whereBetween':
                        $key = array_shift($arguments[$whereArg]);
                        $collection = $collection->whereBetween($key, $arguments[$whereArg]);
                        break;

                    case 'whereNotBetween':
                        $key = array_shift($arguments[$whereArg]);
                        $collection = $collection->whereNotBetween($key, $arguments[$whereArg]);
                        break;
                }
            }
        }

        // Shuffle or sort or sort-desc or reverse
        if (!empty($arguments['shuffle'])) {
            $collection = $collection->shuffle();
        } else if (!empty($arguments['sortBy'])) {
            $collection = $collection->sortBy($arguments['sortBy']);
        } else if (!empty($arguments['sortByDesc'])) {
            $collection = $collection->sortByDesc($arguments['sortByDesc']);
        } else if (!empty($arguments['reverse'])) {
            $collection = $collection->reverse();
        }

        // First, last, except, nth, random, skip
        if (!empty($arguments['first'])) {
            $collection = $collection->shift($arguments['first']);
        } else if (!empty($arguments['last'])) {
            $collection = $collection->pop($arguments['last']);
        } else if (!empty($arguments['except'])) {
            $collection = $collection->except($arguments['except']);
        } else if (!empty($arguments['nth'])) {
            $collection = $collection->nth($arguments['except']);
        } else if (!empty($arguments['random'])) {
            $collection = $collection->random($arguments['random']);
        } else if (!empty($arguments['skip'])) {
            $collection = $collection->skip($arguments['skip']);
        }

        if (is_array($collection)) {
            $collection = new Collection([$collection]);
        }

        return $collection;
    }
}
