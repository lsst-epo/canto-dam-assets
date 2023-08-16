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
    public string $authEndpoint = "https://oauth.canto.com/oauth/api/oauth2/token?app_id={appId}&app_secret={secretKey}&grant_type=client_credentials&refresh_token=";
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
            ['authEndpoint', 'default', 'value' => 'https://oauth.canto.com/oauth/api/oauth2/token?app_id={appId}&app_secret={secretKey}&grant_type=client_credentials&refresh_token='],
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
                'attributes' => [
                    'appId',
                    'authEndpoint',
                    'retrieveAssetMetadataEndpoint',
                    'secretKey'
                ],
            ],
        ];
    }
}
