<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\base\FieldInterface;
use craft\db\Table;
use craft\fields\Matrix;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\models\CantoFieldData;
use yii\base\Component;
use yii\db\Exception;

/**
 * Assets service
 */
class Assets extends Component
{
    public const CONTENT_COLUMN_KEY_MAPPINGS = [
        'cantoId' => null,
        'cantoAlbumId' => 'cantoAlbumId',
        'cantoAssetData' => 'cantoAssetData',
        'cantoAlbumData' => 'cantoAlbumData',
    ];

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

    public function deleteByCantoId(string $cantoId)
    {
        $this->deleteEntryContentByCantoId($cantoId);
        $this->deleteMatrixContentByCantoId($cantoId);
        $this->deleteSuperTableContentByCantoId($cantoId);
        $this->deleteNeoContentByCantoId($cantoId);
    }
    
    protected function deleteEntryContentByCantoId(string $cantoId)
    {
        $fields = Craft::$app->getFields()->getFieldsByType(CantoDamAsset::class);
        $cantoFieldData = Craft::createObject([
            'class' => CantoFieldData::class,
            'cantoId' => null,
            'cantoAlbumId' => null,
            'cantoAssetData' => [],
            'cantoAlbumData' => [],
        ]);
        $this->updateContentByCantoId($cantoId, $cantoFieldData, $fields, Table::CONTENT);
    }

    protected function deleteMatrixContentByCantoId(string $cantoId)
    {
        $fields = Craft::$app->getFields()->getFieldsByType(Matrix::class);
        foreach ($fields as $field) {
            $matrixContentTableName = Craft::$app->getMatrix()->defineContentTableName($field);
        }
    }

    protected function deleteSuperTableContentByCantoId(string $cantoId)
    {
    }

    protected function deleteNeoContentByCantoId(string $cantoId)
    {
    }

    /**
     * Update the $cantoId entry in the $table for the $cantoDamAssetFields with $cantoFieldData
     *
     * @param string $cantoId
     * @param CantoFieldData $cantoFieldData
     * @param FieldInterface[] $cantoDamAssetFields
     * @param string $table
     * @return void
     */
    protected function updateContentByCantoId(string $cantoId, CantoFieldData $cantoFieldData, array $cantoDamAssetFields, string $table): void
    {
        $columnKey = self::CONTENT_COLUMN_KEY_MAPPINGS['cantoId'] ?? null;
        foreach ($cantoDamAssetFields as $cantoDamAssetField) {
            $queryColumn = ElementHelper::fieldColumnFromField($cantoDamAssetField, $columnKey);
            $columns = [];
            foreach (self::CONTENT_COLUMN_KEY_MAPPINGS as $propertyName => $selectColumnKey) {
                $columns[ElementHelper::fieldColumnFromField($cantoDamAssetField, $selectColumnKey)] = $cantoFieldData->$propertyName;
            }
            if ($queryColumn) {
                try {
                    $rows = Db::update($table, $columns, [$queryColumn => $cantoId]);
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
        }
    }
}
