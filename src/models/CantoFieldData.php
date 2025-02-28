<?php

namespace lsst\cantodamassets\models;

use craft\base\Model;
use craft\validators\ArrayValidator;
use lsst\cantodamassets\lib\laravel\Collection;
use yii\helpers\Inflector;

/**
 * Canto DAM Field Data
 */
class CantoFieldData extends Model
{
    /**
     * Which fields have their properties camelized to be compatible with GraphQL query params
     */
    public const CAMELIZED_FIELDS = [
        'metadata',
        'additional',
        'default',
    ];

    public ?string $cantoId = null;
    public ?string $cantoAlbumId = null;
    public Collection|array $cantoAssetData = [];
    public Collection|array $cantoAlbumData = [];

    public function __construct($config = [])
    {
        if (empty($config['cantoAssetData'])) {
            $config['cantoAssetData'] = [];
        }
        if (empty($config['cantoAlbumData'])) {
            $config['cantoAlbumData'] = [];
        }

        parent::__construct($config);
    }

    public function init(): void
    {
        parent::init();
        // Make sure we camelize the keys if an array is being returned, since we normalize them to be camelized
        // as GraphQL doesn't support spaces or other special characters in the query params
        foreach ($this->cantoAssetData as &$item) {
            foreach (self::CAMELIZED_FIELDS as $fieldName) {
                if (isset($item[$fieldName])) {
                    foreach ($item[$fieldName] as $key => $value) {
                        unset($item[$fieldName][$key]);
                        $item[$fieldName][Inflector::camelize($key)] = $value;
                    }
                }
            }
        }

        $this->cantoAssetData = new Collection($this->cantoAssetData);
        $this->cantoAlbumData = new Collection($this->cantoAlbumData);
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            [
                [
                    'cantoId',
                    'cantoAlbumId',
                ],
                [
                    'string',
                    'skipOnEmpty' => true,
                ],
            ],
            [
                [
                    'cantoAlbumData',
                    'cantoAssetData',
                ],
                'default',
                'value' => [],
            ],
            [
                [
                    'cantoAlbumData',
                    'cantoAssetData',
                ],
                ArrayValidator::class,
            ],
        ];
    }
}
