<?php

namespace lsst\cantodamassets\gql\interfaces;

use Craft;
use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use lsst\cantodamassets\gql\types\generators\CantoDamAssetGenerator;
use yii\helpers\Inflector;

class CantoDamAssetInterface extends BaseInterfaceType
{
    public static function getTypeGenerator(): string
    {
        return CantoDamAssetGenerator::class;
    }

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

    public static function getName(): string
    {
        return 'CantoDamAssetInterface';
    }

    public static function getFieldDefinitions(): array
    {
        return Craft::$app->getGql()->prepareFieldDefinitions(array_merge(parent::getFieldDefinitions(),
            // Fields from the cantoAPI.getDetail() API endpoint
            [
                'metadata' => new ObjectType([
                    'name' => 'CantoMetadataType',
                    'fields' => self::camelizeArray([
                        'Bits Per Pixel' => Type::string(),
                        'File Type Detail' => Type::string(),
                        'File Type Extension' => Type::string(),
                        'Flight File Extension' => Type::string(),
                        'GIF Version' => Type::string(),
                        'Background Color' => Type::string(),
                        'Animation Iterations' => Type::string(),
                        'WHRotated' => Type::string(),
                        'RSize' => Type::string(),
                        'Flight File Type' => Type::string(),
                        'File Name' => Type::string(),
                        'Color Resolution Depth' => Type::string(),
                        'Comment' => Type::string(),
                        'Create Date' => Type::string(),
                        'File Inode Change Date/Time' => Type::string(),
                        'File Type' => Type::string(),
                        'Image Size' => Type::string(),
                        'File Access Date/Time' => Type::string(),
                        'Image Height' => Type::string(),
                        'Orientation' => Type::string(),
                        'Image Width' => Type::string(),
                        'Duration time' => Type::string(),
                        'File Modification Date/Time' => Type::string(),
                        'MIME Type' => Type::string(),
                        'Finfotool version' => Type::string(),
                        'Asset Data Size (Long)' => Type::string(),
                        'Megapixels' => Type::string(),
                        'Has Color Map' => Type::string(),
                        'Frame Count' => Type::string(),
                        'Panoramas' => Type::string(),
                    ])
                ]),
                'height' => Type::string(),
                'relatedAlbums' => Type::listOf(new ObjectType([
                    'name' => 'CantoRelatedAlbumsType',
                    'fields' => [
                        'height' => Type::string(),
                        'dpi' => Type::string(),
                        'idPath' => Type::string(),
                        'namePath' => Type::string(),
                        'url' => new ObjectType([
                            'name' => 'CantoRelatedAlbumsUrlType',
                            'fields' => [
                                'detail' => Type::string(),
                            ],
                        ]),
                        'width' => Type::string(),
                        'name' => Type::string(),
                        'id' => Type::string(),
                        'size' => Type::string(),
                        'schema' => Type::string(),
                    ]
                ])),
                'md5' => Type::string(),
                'approvalStatus' => Type::string(),
                'ownerName' => Type::string(),
                'smartTags' => Type::listOf(Type::string()),
                'dpi' => Type::string(),
                'lastUploaded' => Type::string(),
                'versionHistory' => Type::listOf(new ObjectType([
                    'name' => 'CantoVersionHistoryType',
                    'fields' => [
                        'no' => Type::string(),
                        'ownerName' => Type::string(),
                        'created' => Type::string(),
                        'time' => Type::string(),
                        'version' => Type::string(),
                        'comment' => Type::string(),
                        'uri' => new ObjectType([
                            'name' => 'CantoVersionHistoryUriType',
                            'fields' => [
                                'preview' => Type::string(),
                                'download' => Type::string(),
                            ],
                        ]),
                        'currentVersion' => Type::string(),
                    ]
                ])),
                'created' => Type::string(),
                'keyword' => Type::listOf(Type::string()),
                'time' => Type::string(),
                'tag' => Type::listOf(Type::string()),
                'additional' => new ObjectType([
                    'name' => 'CantoAdditionalType',
                    'fields' => self::camelizeArray([
                        'Description' => Type::string(),
                        'Uploaded by' => Type::string(),
                        'WebDAM Group ID' => Type::string(),
                        'Spatial Reference Value' => Type::string(),
                        'Spatial Coordinate System Projection' => Type::string(),
                        'Metadata Version' => Type::string(),
                        'Uploader contact' => Type::string(),
                        'Credit' => Type::string(),
                        'WebDAM Publisher ID' => Type::string(),
                        'Alt Text **ES**' => Type::string(),
                        'Publisher ID' => Type::string(),
                        'Spatial Reference Dimension' => Type::string(),
                        'WebDAM Publisher' => Type::string(),
                        'Alt Text **EN**' => Type::string(),
                        'ID' => Type::string(),
                        'Social Media Description' => Type::string(),
                        'Title **ES**' => Type::string(),
                        'Title **EN**' => Type::string(),
                        'Media Consent' => Type::string(),
                        'Spatial Rotation' => Type::string(),
                        'Title' => Type::string(),
                        'Publisher' => Type::string(),
                        'Spatial Scale' => Type::string(),
                        'Spatial Reference Pixel' => Type::string(),
                        'WebDAM Sublocation' => Type::string(),
                        'Spatial Coordinate Frame' => Type::string(),
                        'Caption **ES**' => Type::string(),
                        'Type' => Type::string(),
                        'Social Media Handles' => Type::string(),
                        'Caption **EN**' => Type::string(),
                        'Usage Terms' => Type::string(),
                        'WebDAM Media Type' => Type::string(),
                    ])
                ]),
                'url' => new ObjectType([
                    'name' => 'CantoUrlType',
                    'fields' => [
                        'preview' => Type::string(),
                        'download' => Type::string(),
                        'metadata' => Type::string(),
                        'HighJPG' => Type::string(),
                        'PNG' => Type::string(),
                        'directUrlOriginal' => Type::string(),
                        'detail' => Type::string(),
                        'directUrlPreview' => Type::string(),
                        'LowJPG' => Type::string(),
                    ]
                ]),
                'width' => Type::string(),
                'name' => Type::string(),
                'default' => new ObjectType([
                    'name' => 'CantoDefaultType',
                    'fields' => self::camelizeArray([
                        'Size' => Type::string(),
                        'Uploaded by' => Type::string(),
                        'Dimensions' => Type::string(),
                        'GPS' => Type::string(),
                        'Date uploaded' => Type::string(),
                        'Date modified' => Type::string(),
                        'Name' => Type::string(),
                        'Copyright' => Type::string(),
                        'Modified by' => Type::string(),
                        'LowJPG' => Type::string(),
                        'Content Type' => Type::string(),
                        'Author' => Type::string(),
                        'Date Created' => Type::string(),
                        'Resolution' => Type::string(),
                    ])
                ]),
                'id' => Type::string(),
                'size' => Type::string(),
                'scheme' => Type::string(),
                'owner' => Type::string(),
            ],
            // Fields from the asset directuri API endpoint
            [
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
            ]),
            CantoDamAssetGenerator::getName());
    }

    /**
     * Used to convert the Canto-returned keys into GraphQL compliant field names
     *
     * @param $array
     * @return array
     */
    private static function camelizeArray($array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = [
                'name' => Inflector::camelize($key),
                'type' => $value,
            ];
        }
        return $result;
    }
}
