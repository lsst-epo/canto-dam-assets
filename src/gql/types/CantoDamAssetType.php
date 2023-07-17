<?php

namespace lsst\cantodamassets\gql\types;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;

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
        if (is_string($source)) {
            return $source;
        }
        $fieldName = $resolveInfo->fieldName;
        return $source[$fieldName];
    }
}
