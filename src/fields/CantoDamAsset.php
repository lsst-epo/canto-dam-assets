<?php

namespace lsst\cantodamassets\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\db\mysql\Schema as MySqlSchema;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;
use lsst\cantodamassets\gql\resolvers\CantoDamAssetResolver;
use lsst\cantodamassets\models\CantoFieldData;
use yii\db\Schema;

/**
 * Canto Dam Asset field type
 */
class CantoDamAsset extends Field implements PreviewableFieldInterface
{
    public static function displayName(): string
    {
        return Craft::t('_canto-dam-assets', 'Canto Dam Asset');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public function getContentGqlType(): Type|array
    {
        return [
            'name' => $this->handle,
            'description' => 'Canto Dam Asset field',
            'args' => $this->getGqlArguments(),
            'type' => Type::listOf(CantoDamAssetInterface::getType()),
            'resolve' => CantoDamAssetResolver::class . '::resolve',
        ];
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function getContentColumnType(): array|string
    {
        return [
            'cantoId' => Schema::TYPE_STRING,
            'cantoAlbumId' => Schema::TYPE_STRING,
            'cantoAssetData' => Craft::$app->getDb()->getIsMysql() ? MySqlSchema::TYPE_MEDIUMTEXT : Schema::TYPE_TEXT,
            'cantoAlbumData' => Schema::TYPE_STRING,
        ];
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): array
    {
        return [
            'cantoId' => $value['cantoId'] ?? null,
            'cantoAlbumId' => $value['cantoAlbumId'] ?? null,
            'cantoAssetData' => $value['cantoAssetData'] ?? null,
            'cantoAlbumData' => $value['cantoAlbumData'] ?? null,
        ];
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        if ($value === null) {
            $value = new CantoFieldData();
        }
        if (is_array($value)) {
            $value['cantoAssetData'] = Json::decodeIfJson($value['cantoAssetData']);
            $value['cantoAlbumData'] = Json::decodeIfJson($value['cantoAlbumData']);
            $value = new CantoFieldData($value);
        }
        return $value;
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'fieldId' => $this->id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').CantoDamConnector(" . $jsonVars . ");");
        // In case we want to try to transform this image
        $previewUrl = $value->cantoAssetData[0]['url']['directUrlOriginal'] ?? null;
        // The name to subtitle the preview
        $assetCount = count($value->cantoAssetData);
        $previewName = $value->cantoId == 0 ? "{$assetCount} images" : $value->cantoAssetData[0]['displayName'] ?? null;
        $albumName = $value->cantoAlbumData['name'] ?? '';
        // Render the input template
        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/CantoDamAsset_input.twig',
            [
                'name' => $this->handle,
                'value' => $value,
                'fieldId' => $this->id,
                'elementId' => $element->id ?? null,
                'id' => $id,
                'element' => Json::encode($element),
                'namespacedId' => $view->namespaceInputId($id),
                'previewUrl' => $previewUrl,
                'previewName' => $previewName,
                'albumName' => $albumName,
                'assetCount' => $assetCount,
                'accessToken' => CantoDamAssets::$plugin->assets->getAuthToken()
            ]
        );
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        // In case we want to try to transform this image
        $previewUrl = $value->cantoAssetData[0]['url']['directUrlOriginal'] ?? null;
        // The name to subtitle the preview
        $assetCount = count($value->cantoAssetData);
        $previewName = $value->cantoId == 0 ? "{$assetCount} images" : $value->cantoAssetData[0]['displayName'] ?? null;
        $albumName = $value->cantoAlbumData['name'] ?? '';
        $className = $value->cantoId == 0 ? "canto-asset-preview-stack" : "";

        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/CantoDamAsset_preview.twig',
            [
                'name' => $this->handle,
                'value' => $value,
                'className' => $className,
                'fieldId' => $this->id,
                'elementId' => $element->id ?? null,
                'element' => Json::encode($element),
                'previewUrl' => $previewUrl,
                'previewName' => $previewName,
                'albumName' => $albumName,
                'assetCount' => $assetCount,
            ]
        );

    }

    public function getElementValidationRules(): array
    {
        return [];
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        /* @var CantoFieldData $value */
        $keywords = $value->cantoAssetData->flatten()->values()->filter();
        return StringHelper::toString($keywords, ' ');
    }

    public function getElementConditionRuleType(): array|string|null
    {
        return null;
    }

    /**
     * @inerhitDoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        // By default this method will allow searching on the primary content column for this field type,
        // which is `cantoId`, but this stub method is left in place in case we need to do some other kind
        // of custom searching in the future
        parent::modifyElementsQuery($query, $value);
    }

    protected function getGqlArguments(): array
    {
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
                'description' => 'Sort the collection using the sort string(s). You can use the `field.subField` syntax for nested fields and provide multiple sort commands as a list of strings.',
                'type' => Type::listOf(Type::string()),
            ],
            'sortByDesc' => [
                'name' => 'sortByDesc',
                'description' => 'Sort the collection using the sort string(s) in a descending order. You can use the `field.subField` syntax for nested fields and provide multiple sort commands as a list of strings.',
                'type' => Type::listOf(Type::string()),
            ],
            'forPage' => [
                'name' => 'forPage',
                'description' => 'Paginate the items by page number and items per page. (See https://laravel.com/docs/10.x/collections#method-forpage).',
                'type' => new InputObjectType([
                    'name' => 'ForPageInput',
                    'fields' => [
                        'page' => [
                            'type' => Type::int(),
                            'description' => 'The page number'
                        ],
                        'items' => [
                            'type' => Type::int(),
                            'description' => 'The number of items per page'
                        ],
                    ]
                ]),
            ],
            'where' => [
                'name' => 'where',
                'description' => 'Get all items by the given key value pair, using the optional operator for comparison. (See https://laravel.com/docs/10.x/collections#method-where).',
                'type' => new InputObjectType([
                    'name' => 'WhereFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields'
                        ],
                        'value' => [
                            'type' => Type::string(),
                            'description' => 'The value to match when searching'
                        ],
                        'operator' => [
                            'type' => Type::string(),
                            'description' => 'The comparison operator to use, e.g.: `=`, `>`, `<=`, etc. The default is `=`'
                        ]
                    ]
                ]),
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
                'type' => new InputObjectType([
                    'name' => 'WhereInFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields'
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values that should be in the key'
                        ],
                    ]
                ]),
            ],
            'whereNotIn' => [
                'name' => 'whereNotIn',
                'description' => 'Filter items by the given key value pair, making sure the value is NOT in the array. (See https://laravel.com/docs/10.x/collections#method-wherenotin).',
                'type' => new InputObjectType([
                    'name' => 'WhereNotInFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields'
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The the values that should not be in the key'
                        ],
                    ]
                ]),
            ],
            'whereBetween' => [
                'name' => 'whereBetween',
                'description' => 'Filter items such that the value of the given key is between the given values. (See https://laravel.com/docs/10.x/collections#method-wherebetween).',
                'type' => new InputObjectType([
                    'name' => 'WhereBetweenFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields'
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values that the key should be between'
                        ],
                    ]
                ]),
            ],
            'whereNotBetween' => [
                'name' => 'whereNotBetween',
                'description' => 'Filter items such that the value of the given key is not between the given values.  (See https://laravel.com/docs/10.x/collections#method-wherenotbetween).',
                'type' => new InputObjectType([
                    'name' => 'WhereNotBetweenFiltersInput',
                    'fields' => [
                        'key' => [
                            'type' => Type::string(),
                            'description' => 'The key to search on, you can use the `field.subField` syntax for nested fields'
                        ],
                        'values' => [
                            'type' => Type::listOf(Type::string()),
                            'description' => 'The values the key should not be between'
                        ],
                    ]
                ]),
            ],
        ], 'CantoDamAssetQueryType');
    }
}
