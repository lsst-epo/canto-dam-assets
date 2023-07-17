<?php

namespace lsst\cantodamassets\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;
use GraphQL\Type\Definition\Type;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\gql\types\CantoDamAssetType;

class CantoDamAssetGenerator implements GeneratorInterface
{

    public static function generateTypes($context = null): array
    {
        /** @var CantoDamAsset $context */
        $typeName = self::getName($context);

        $cantoDamAssetFields = [
            // static fields
            'displayName' => [
                'name' => 'displayName',
                'description' => 'The display name of the Canto asset',
                'type' => Type::string(),
            ],
            'directUri' => [
                'name' => 'directUri',
                'description' => 'The URI to the Canto asset',
                'type' => Type::string(),
            ],
            'id' => [
                'name' => 'id',
                'description' => 'The ID of the Canto asset',
                'type' => Type::string(),
            ],
            'preview' => [
                'name' => 'preview',
                'description' => 'The embed preview of the Canto asset',
                'type' => Type::string(),
            ],
            'previewUri' => [
                'name' => 'previewUri',
                'description' => 'The preview URI to the Canto asset',
                'type' => Type::string(),
            ],
            'scheme' => [
                'name' => 'scheme',
                'description' => 'The scheme of the Canto asset',
                'type' => Type::string(),
            ],
            'size' => [
                'name' => 'size',
                'description' => 'The size of the Canto asset',
                'type' => Type::string(),
            ],
        ];
        $cantoFieldDataType = GqlEntityRegistry::getEntity($typeName)
            ?: GqlEntityRegistry::createEntity($typeName, new CantoDamAssetType([
                'name' => $typeName,
                'description' => 'This entity has all the CantoDamAsset properties',
                'fields' => function () use ($cantoDamAssetFields) {
                    return $cantoDamAssetFields;
                },
            ]));

        TypeLoader::registerType($typeName, function () use ($cantoFieldDataType) {
            return $cantoFieldDataType;
        });

        return [$cantoFieldDataType];
    }

    public static function getName($context = null): string
    {
        /** @var CantoDamAsset $context */
        return $context->handle . '_CantoFieldData';
    }
}
