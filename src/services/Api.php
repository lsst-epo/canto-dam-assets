<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\helpers\Json;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\models\CantoFieldData;
use yii\base\Component;

/**
 * Assets service
 *
 * @property-read array[] $apiHeaders
 */
class Api extends Component
{
    /**
     * @var ?string
     */
    private ?string $authToken = null;

    /**
     * Return the auth token using the app ID and secret key
     *
     * @return string
     */
    public function getAuthToken(): string
    {
        if ($this->authToken) {
            return $this->authToken;
        }
        $client = Craft::createGuzzleClient();
        $appId = CantoDamAssets::$plugin->getSettings()->getAppId();
        $secretKey = CantoDamAssets::$plugin->getSettings()->getSecretKey();
        $authEndpoint = CantoDamAssets::$plugin->getSettings()->getAuthEndpoint();

        // Inject appId & secretKey tokens in the URL
        $authEndpoint = str_replace(["{appId}", "{secretKey}"], [$appId, $secretKey], $authEndpoint);

        // Get auth token
        try {
            $response = $client->post($authEndpoint);
            $body = $response->getBody();
        } catch (\Throwable $e) {
            Craft::error("An exception occurred in getAuthToken()", __METHOD__);
            return $e->getMessage();
        }

        // Extract auth token from response
        $authTokenDecoded = Json::decodeIfJson($body);
        $this->authToken = $authTokenDecoded["accessToken"];

        return $this->authToken;
    }

    public function fetchFieldDataByCantoId(string $cantoId): ?CantoFieldData
    {
        // @TODO
    }

    public function fetchFieldDataByAlbumId(string $albumId): ?CantoFieldData
    {
        // @TODO
    }

    /**
     * Return the headers for the API endpoint
     *
     * @return array[]
     */
    public function getApiHeaders(): array
    {
        return [
            'headers' => [
                'Authorization' => $this->getAuthToken(),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];
    }
}
