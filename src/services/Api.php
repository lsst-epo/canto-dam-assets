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
    private const MAX_ALBUM_REQUEST_ITEMS = 1000;

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
     * @param array|string[] $params
     * @return array|string[]
     */
    public function cantoApiRequest(string $path, array $params = []): array
    {
        $client = Craft::createGuzzleClient();
        $cantoApiEndpoint =
            rtrim(CantoDamAssets::$plugin->getSettings()->getApiUrl(), '/')
            . '/'
            . ltrim($path, '/');
        $options = array_merge($this->getApiHeaders(), [
            'query' => $params,
        ]);
        try {
            $response = $client->request("GET", $cantoApiEndpoint, $options);
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
                'errorMessage' => 'Canto endpoint failure',
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
            'cantoId' => $responseBody['id'],
            'cantoAlbumId' => 0,
            'cantoAssetData' => [$responseBody],
            'cantoAlbumData' => [
                'id' => $responseBody['relatedAlbums'][0]['id'],
                'name' => $responseBody['relatedAlbums'][0]['name'],
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
        $buffer = [];
        if (!$this->paginatedAlbumRequest($buffer, $albumId, 0)) {
            return null;
        }
        $albumName = $this->albumNameFromRelatedAlbums($buffer[0]['relatedAlbums'] ?? [], $albumId);

        return new CantoFieldData([
            'cantoId' => 0,
            'cantoAlbumId' => $albumId,
            'cantoAssetData' => $buffer,
            'cantoAlbumData' => [
                'id' => $albumId,
                'name' => $albumName,
            ],
        ]);
    }

    /**
     * Retrieve all of the assets from the albumId album, paginated to handle API limits
     *
     * @param array $buffer
     * @param string $albumId
     * @param int $start
     * @return boolean
     */
    public function paginatedAlbumRequest(array &$buffer, string $albumId, int $start = 0): bool
    {
        $params = [
            'sortBy' => 'time',
            'sortDirection' => 'descending',
            'limit' => self::MAX_ALBUM_REQUEST_ITEMS,
            'start' => $start,
        ];
        $responseBody = $this->cantoApiRequest('/album/' . $albumId, $params);
        if (isset($responseBody['status']) && $responseBody['status'] === 'error') {
            return false;
        }
        if (!is_array($responseBody['results'])) {
            return false;
        }
        $buffer = array_merge($buffer, $responseBody['results']);
        if (count($buffer) < $responseBody['found']) {
            $this->paginatedAlbumRequest($buffer, $albumId, $start + self::MAX_ALBUM_REQUEST_ITEMS);
        }

        return true;
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
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * Return the name of an album from $relatedAlbums that matches the $albumId
     *
     * @param array $relatedAlbums
     * @param string $albumId
     * @return string
     */
    protected function albumNameFromRelatedAlbums(array $relatedAlbums, string $albumId): string
    {
        foreach ($relatedAlbums as $album) {
            $idArray = explode('/', $album['idPath']);
            $id = end($idArray);
            if ($id === $albumId) {
                $nameArray = explode('/', $album['namePath']);

                return end($nameArray);
            }
        }

        return '';
    }
}
