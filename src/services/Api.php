<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\helpers\Json;
use GuzzleHttp\Exception\GuzzleException;
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
     * @return ?string
     */
    public function getAuthToken(): ?string
    {
        // If we have the token memoized already, just return it
        if ($this->authToken) {
            return $this->authToken;
        }
        $client = Craft::createGuzzleClient();
        $settings = CantoDamAssets::$plugin->getSettings();
        // Inject appId & secretKey tokens in the URL
        $authEndpoint = str_replace(
            ["{appId}", "{secretKey}"],
            [$settings->getAppId(), $settings->getSecretKey()],
            $settings->getAuthEndpoint()
        );
        // Get auth token
        try {
            $response = $client->post($authEndpoint);
        } catch (\Throwable $e) {
            Craft::error("An exception occurred in getAuthToken()", __METHOD__);

            return $e->getMessage();
        }
        $body = Json::decodeIfJson($response->getBody());
        // Extract auth token from response
        if (is_array($body)) {
            $this->authToken = $body["accessToken"] ?? null;
        }

        return $this->authToken;
    }

    /**
     * Issue an API request to the Canto endpoint with auth
     *
     * @param string $path
     * @return array|string[]
     */
    public function cantoApiRequest(string $path): array
    {
        $client = Craft::createGuzzleClient();
        $cantoApiEndpoint =
            rtrim(CantoDamAssets::$plugin->getSettings()->getApiUrl(), '/')
            . '/'
            . ltrim($path, '/');
        try {
            $response = $client->request("GET", $cantoApiEndpoint, $this->getApiHeaders());
        } catch (GuzzleException $e) {
            Craft::error($e->getMessage(), __METHOD__);

            return [
                "status" => "error",
                'errorMessage' => $e->getMessage(),
            ];
        }
        $body = Json::decodeIfJson($response->getBody());
        if (!is_array($body)) {
            return [
                "status" => "error",
                'errorMessage' => 'Canto endpoint failure'
            ];
        }

        return $body;
    }

    /**
     * Return a CantoFieldData for a Single Image CantoDamAssets field
     *
     * @param string $cantoId
     * @return CantoFieldData|null
     */
    public function fetchFieldDataByCantoId(string $cantoId): ?CantoFieldData
    {
        $responseBody = $this->cantoApiRequest('/image/' . $cantoId);
        if (isset($responseBody['status']) && $responseBody['status'] === 'error') {
            return null;
        }

        return new CantoFieldData([
            'cantoId' => $responseBody['id'] ?? 0,
            'cantoAlbumId' => 0,
            'cantoAssetData' => $responseBody,
            'cantoAlbumData' => [
                'id' => $responseBody['relatedAlbums'][0]['id'] ?? 0,
                'name' => $responseBody['relatedAlbums'][0]['name'] ?? '',
            ],
        ]);
    }

    /**
     * Return a CantoFieldData for a Full Album CantoDamAssets field
     *
     * @param string $albumId
     * @return CantoFieldData|null
     */
    public function fetchFieldDataByAlbumId(string $albumId): ?CantoFieldData
    {
        $responseBody = $this->cantoApiRequest('/album/' . $albumId);
        if (isset($responseBody['status']) && $responseBody['status' === 'error']) {
            return null;
        }
        // @TODO implement fetching a full album's data

        return new CantoFieldData([
            'cantoId' => 0,
            'cantoAlbumId' => 0,
            'cantoAssetData' => [],
            'cantoAlbumData' => [],
        ]);
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
                'Authorization' => 'bearer ' . $this->getAuthToken(),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];
    }
}
