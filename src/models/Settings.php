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
    public string $tenantHostName = "";
    public string $secretKey = "";
    public string $webhookSecureToken = "";

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        // Unset any deprecated properties
        unset($config['retrieveAssetMetadataEndpoint']);
        parent::__construct($config);
    }

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
    public function getTenantHostName(): string
    {
        return App::parseEnv($this->tenantHostName);
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return App::parseEnv($this->secretKey);
    }

    public function getWebhookSecureToken(): string
    {
        return App::parseEnv($this->webhookSecureToken);
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
                    'tenantHostName',
                    'secretKey'
                ],
                'required',
            ],
            [
                [
                    'authEndpoint',
                    'secretKey',
                    'webhookSecureToken',
                    'tenantHostName',
                ],
                'string',
            ],
            [
                [
                    'authEndpoint',
                ],
                'url',
            ],
            [
                'authEndpoint', 'default', 'value' => 'https://oauth.canto.com/oauth/api/oauth2/token?app_id={appId}&app_secret={secretKey}&grant_type=client_credentials&refresh_token='
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
                'attributes' => [
                    'appId',
                    'authEndpoint',
                    'tenantHostName',
                    'secretKey',
                    'webhookSecureToken',
                ],
            ],
        ];
    }
}
