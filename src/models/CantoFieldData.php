<?php

namespace lsst\cantodamassets\models;

use craft\base\Model;
use craft\validators\ArrayValidator;
use Illuminate\Support\Collection;

/**
 * Canto DAM Field Data
 */
class CantoFieldData extends Model
{
    public ?string $cantoId = null;
    public Collection|array $cantoAssetData = [];

    public function init(): void
    {
        parent::init();
        $this->cantoAssetData = new Collection($this->cantoAssetData);
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
                ],
                [
                    'string',
                    'skipOnEmpty' => true
                ],
            ],
            [
                [
                    'cantoAssetData',
                ],
                'default',
                'value' => [],
            ],
            [
                [
                    'cantoAssetData',
                ],
                ArrayValidator::class,
            ],
        ];
    }
}
