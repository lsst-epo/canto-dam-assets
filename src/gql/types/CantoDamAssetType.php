<?php

namespace lsst\cantodamassets\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;
use lsst\cantodamassets\lib\laravel\Collection;
use yii\helpers\Inflector;

class CantoDamAssetType extends ObjectType
{
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            CantoDamAssetInterface::getType(),
        ];

        parent::__construct($config);
    }

    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;
        $resolvedData = $source[$fieldName];
        // Make sure we camelize the keys if an array is being returned, since we normalize them to be camelized
        // as GraphQL doesn't support spaces or other special characters in the query params
        if (is_array($resolvedData)) {
            $collection = new Collection($resolvedData);
            $resolvedData = $collection->mapWithKeys(fn($value, $key) => [Inflector::camelize($key) => $value])->all();
        }
        return $resolvedData ?? null;
    }
}
