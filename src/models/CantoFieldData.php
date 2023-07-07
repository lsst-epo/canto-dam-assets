<?php

namespace lsst\cantodamassets\models;

use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * Canto DAM Field Data
 */
class CantoFieldData extends Model
{
    public ?string $cantoId = null;
    public array $cantoAssetData = [];

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
