<?php

namespace lsst\cantodamassets\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;
use lsst\cantodamassets\gql\types\CantoDamAssetType;

class CantoDamAssetGenerator implements GeneratorInterface
{
    public static function generateTypes(mixed $context = null): array
    {
        $gqlTypes = [];
        $cantoDamAssetFields = CantoDamAssetInterface::getFieldDefinitions();
        $typeName = self::getName();
        $cantoDamAssetType = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new CantoDamAssetType([
                'name' => $typeName,
                'fields' => function() use ($cantoDamAssetFields) {
                    return $cantoDamAssetFields;
                },
                'description' => 'This entity has all the Canto Dam Asset fields',
            ]));

        $gqlTypes[$typeName] = $cantoDamAssetType;
        TypeLoader::registerType($typeName, function() use ($cantoDamAssetType) {
            return $cantoDamAssetType;
        });

        return $gqlTypes;
    }

    public static function getName($context = null): string
    {
        return 'CantoDamAssetType';
    }
}
