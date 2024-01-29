<?php

namespace lsst\cantodamassets\models;

use craft\base\Model;
use craft\validators\ArrayValidator;
use lsst\cantodamassets\lib\laravel\Collection;

/**
 * Canto DAM Field Data
 */
class CantoFieldData extends Model
{
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
