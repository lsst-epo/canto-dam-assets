<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\helpers\Json;
use lsst\cantodamassets\CantoDamAssets;
use yii\base\Component;

/**
 * Assets service
 */
class Assets extends Component
{
    /**
     *  Private function for using the app ID and secret key to get an auth token
     */
    public function getAuthToken($validateOnly = false): string
    {
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
            return $e->getMessage();
        }


        // Extract auth token from response
        if (!$validateOnly) {
            $authTokenDecoded = Json::decodeIfJson($body);

            return $authTokenDecoded["accessToken"];
        }
        Craft::error("An exception occurred in getAuthToken()", __METHOD__);

        return Craft::t("_canto-dam-assets", "An error occurred fetching auth token!");
    }
}
