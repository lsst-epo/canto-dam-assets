<?php

namespace lsst\cantodamassets\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

/**
 * Canto DAM Assets settings
 */
class Settings extends Model
{
    public string $appId = "";
    public string $authEndpoint = "";
    public string $retrieveAssetMetadataEndpoint = "";
    public string $secretKey = "";

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return App::parseEnv($this->appId);
    }

    /**
     * @return string
     */
    public function getAuthEndpoint(): string
    {
        return App::parseEnv($this->authEndpoint);
    }

    /**
     * @return string
     */
    public function getRetrieveAssetMetadataEndpoint(): string
    {
        return App::parseEnv($this->retrieveAssetMetadataEndpoint);
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return App::parseEnv($this->secretKey);
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            [
                [
                    'appId',
                    'authEndpoint',
                    'retrieveAssetMetadataEndpoint',
                    'secretKey'
                ],
                'required',
            ],
            [
                [
                    'authEndpoint',
                    'secretKey'
                ],
                'string',
            ],
            [
                [
                    'authEndpoint',
                    'retrieveAssetMetadataEndpoint',
                ],
                'url',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['secretKey'],
            ],
        ];
    }
}
