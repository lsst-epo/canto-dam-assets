<?php

namespace lsst\cantodamassets\gql\interfaces;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use lsst\cantodamassets\gql\types\generators\CantoDamAssetGenerator;

class CantoDamAssetInterface extends BaseInterfaceType
{
    public static function getTypeGenerator(): string
    {
        return CantoDamAssetGenerator::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by CantoDamAsset.',
            'resolveType' => function (array $value) {
                return GqlEntityRegistry::getEntity(CantoDamAssetGenerator::getName());
            },
        ]));
        CantoDamAssetGenerator::generateTypes();

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'CantoDamAssetInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), [
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
                'type' => Type::int(),
            ],
        ]);
    }
}
