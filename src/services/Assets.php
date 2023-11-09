<?php

namespace lsst\cantodamassets\services;

use benf\neo\Field as NeoField;
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
use verbb\supertable\fields\SuperTableField;
use yii\base\Component;
use yii\base\InvalidConfigException;
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

    /**
     * Update the $cantoId asset from any fields that contain it with the data in $cantoFieldData
     *
     * @param string $cantoId
     * @param CantoFieldData $cantoFieldData
     * @return void
     */
    public function updateByCantoId(string $cantoId, CantoFieldData $cantoFieldData): void
    {
        $this->update($cantoId, $cantoFieldData, 'cantoId');
    }

    /**
     * Update the $albumId asset from any fields that contain it with the data in $cantoFieldData
     *
     * @param string $albumId
     * @param CantoFieldData $cantoFieldData
     * @return void
     */
    public function updateByAlbumId(string $albumId, CantoFieldData $cantoFieldData): void
    {
        $this->update($albumId, $cantoFieldData, 'albumId');
    }

    /**
     * Delete the $cantoId from any fields that contain it
     *
     * @param string $cantoId
     * @return void
     * @throws InvalidConfigException
     */
    public function deleteByCantoId(string $cantoId): void
    {
        $this->delete($cantoId, 'cantoId');
    }

    /**
     * Delete the $cantoId from any fields that contain it
     *
     * @param string $albumId
     * @return void
     * @throws InvalidConfigException
     */
    public function deleteByAlbumId(string $albumId): void
    {
        $this->delete($albumId, 'albumId');
    }

    /**
     * Update the Canto Asset whose $columnKey matches $value with $cantoFieldData, in any fields that contain it
     *
     * @param string $value
     * @param CantoFieldData $cantoFieldData
     * @param $columnKey
     * @return void
     */
    public function update(string $value, CantoFieldData $cantoFieldData, $columnKey): void
    {
        $this->updateEntryContent($value, $cantoFieldData, $columnKey);
        $this->updateMatrixContent($value, $cantoFieldData, $columnKey);
        $this->updateSuperTableContent($value, $cantoFieldData, $columnKey);
        $this->updateNeoContent($value, $cantoFieldData, $columnKey);
    }

    /**
     * Delete the Canto Asset whose $columnKey matches $value, from any fields that contain it
     *
     * @param string $value
     * @param $columnKey
     * @return void
     * @throws InvalidConfigException
     */
    protected function delete(string $value, $columnKey): void
    {
        // Create a CantoFieldData object with empty values, to effectively deleting it
        $cantoFieldData = Craft::createObject([
            'class' => CantoFieldData::class,
            'cantoId' => null,
            'cantoAlbumId' => null,
            'cantoAssetData' => [],
            'cantoAlbumData' => [],
        ]);
        $this->updateEntryContent($value, $cantoFieldData, $columnKey);
        $this->updateMatrixContent($value, $cantoFieldData, $columnKey);
        $this->updateSuperTableContent($value, $cantoFieldData, $columnKey);
        $this->updateNeoContent($value, $cantoFieldData, $columnKey);
    }

    protected function updateEntryContent(string $cantoId, CantoFieldData $cantoFieldData, ?string $columnKey,): void
    {
        $fields = Craft::$app->getFields()->getFieldsByType(CantoDamAsset::class);
        $this->updateContent($cantoId, $cantoFieldData, $columnKey, $fields, Table::CONTENT);
    }

    /* @TODO implement updateMatrixContentByCantoId() */
    protected function updateMatrixContent(string $cantoId, CantoFieldData $cantoFieldData, ?string $columnKey,): void
    {
        $fields = Craft::$app->getFields()->getFieldsByType(Matrix::class);
        foreach ($fields as $field) {
            /* @var Matrix $field */
            $contentTableName = Craft::$app->getMatrix()->defineContentTableName($field);
        }
    }

    /* @TODO implement updateSuperTableContentByCantoId() */
    protected function updateSuperTableContent(string $cantoId, CantoFieldData $cantoFieldData, ?string $columnKey,): void
    {
        $fields = Craft::$app->getFields()->getFieldsByType(SuperTableField::class);
        foreach ($fields as $field) {
            /* @var SuperTableField $field */
        }
    }

    /* @TODO implement updateNeoContentByCantoId() */
    protected function updateNeoContent(string $cantoId, CantoFieldData $cantoFieldData, ?string $columnKey,): void
    {
        $fields = Craft::$app->getFields()->getFieldsByType(NeoField::class);
        foreach ($fields as $field) {
            /* @var NeoField $field */
        }
    }

    /**
     * Update the $cantoId entry in the $table for the $cantoDamAssetFields with $cantoFieldData
     *
     * @param string $cantoId
     * @param CantoFieldData $cantoFieldData
     * @param string|null $columnKey
     * @param FieldInterface[] $cantoDamAssetFields
     * @param string $table
     * @return void
     */
    protected function updateContent(string $cantoId, CantoFieldData $cantoFieldData, ?string $columnKey, array $cantoDamAssetFields, string $table): void
    {
        $columnKey = self::CONTENT_COLUMN_KEY_MAPPINGS[$columnKey] ?? null;
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
