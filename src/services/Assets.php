<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\Json;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\lib\laravel\Collection;
use lsst\cantodamassets\models\CantoFieldData;
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
        $this->update($albumId, $cantoFieldData, 'cantoAlbumId');
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
        $this->delete($albumId, 'cantoAlbumId');
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
        // Create a CantoFieldData object with empty values, to effectively delete it
        $cantoFieldData = Craft::createObject([
            'class' => CantoFieldData::class,
            'cantoId' => null,
            'cantoAlbumId' => null,
            'cantoAssetData' => [],
            'cantoAlbumData' => [],
        ]);
        $this->updateEntryContent($value, $cantoFieldData, $columnKey);
    }

    /**
     * Update entry content in the Content table where the $columnKey matches $value with the $cantoFieldData
     *
     * @param string $value
     * @param CantoFieldData $cantoFieldData
     * @param string|null $columnKey
     * @return void
     */
    protected function updateEntryContent(string $value, CantoFieldData $cantoFieldData, ?string $columnKey): void
    {
        $fields = [];
        $entries = Craft::$app->getEntries();
        $entryTypes = $entries->getAllEntryTypes();
        foreach ($entryTypes as $entryType) {
            $customFields = $entryType->getCustomFields();
            foreach ($customFields as $customField) {
                if ($customField instanceof CantoDamAsset) {
                    $fields[] = $customField;
                }
            }
        }

        $this->updateContent($value, $cantoFieldData, $columnKey, $fields, Table::ELEMENTS_SITES);
    }

    /**
     * Update the $columnKey that matches $value in the $table for the $cantoDamAssetFields with $cantoFieldData
     *
     * @param string $value
     * @param CantoFieldData $cantoFieldData
     * @param string|null $columnKey
     * @param FieldInterface[] $cantoDamAssetFields
     * @param string $table
     * @return void
     */
    protected function updateContent(string $value, CantoFieldData $cantoFieldData, ?string $columnKey, array $cantoDamAssetFields, string $table): void
    {
        $db = Craft::$app->getDb();
        $qb = $db->getQueryBuilder();
        foreach ($cantoDamAssetFields as $cantoDamAssetField) {
            // The layout element uid is the key in the content json
            $fieldUid = $cantoDamAssetField->layoutElement->uid;
            // Compose a JSON object search needle
            $jsonSearchNeedle = [
                $fieldUid => [
                    $columnKey => $value
                ]
            ];
            // Query the db for all the rows that meet the search nest json query
            $jsonSearchSql = $qb->jsonContains('content', $jsonSearchNeedle);
            $rows = (new Query())
                ->select(['id', 'content'])
                ->from([$table])
                ->where($jsonSearchSql)
                ->all();
            // Iterate through the rows, replacing the appropriate value
            foreach ($rows as $row) {
                $content = Json::decodeIfJson($row['content']);
                foreach (self::CONTENT_COLUMN_KEY_MAPPINGS as $propertyName => $selectColumnKey) {
                    $content[$fieldUid][$propertyName] = $cantoFieldData->$propertyName;
                }
                $content = Json::encode($content);
                try {
                    $rowsAffected = Db::update($table, ['content' => $content], ['id' => $row['id']]);
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
            // If the column we're updating is the `cantoId`, we need to search the JSON contents of `cantoAssetData`
            // in order to update any canto assets contained within the JSON blobs as well
            if ($columnKey === 'cantoId') {
                // Compose a JSON object search needle
                $jsonSearchNeedle = [
                    $fieldUid => [
                        'cantoId' => 0,
                        'cantoAssetData' => [
                            'id' => $cantoFieldData->cantoId ?? $value
                        ]
                    ]
                ];
                // Query the db for all the rows that meet the search nest json query
                $jsonSearchSql = $qb->jsonContains('content', $jsonSearchNeedle);
                $rows = (new Query())
                    ->select(['id', 'content'])
                    ->from([$table])
                    ->where($jsonSearchSql)
                    ->all();
                // Iterate through the rows, replacing the appropriate value
                foreach ($rows as $row) {
                    $content = Json::decodeIfJson($row['content']);
                    $assetData = new Collection($content[$fieldUid]['cantoAssetData']);
                    $assetData->transform(function($item) use ($cantoFieldData, $value) {
                        if ($item['id'] === ($cantoFieldData->cantoId ?? $value)) {
                            $item = $cantoFieldData->cantoAssetData[0] ?? [];
                        }
                        return $item;
                    });
                    $content[$fieldUid]['cantoAssetData'] = $assetData->all();
                    $content = Json::encode($content);
                    try {
                        $rowsAffected = Db::update($table, ['content' => $content], ['id' => $row['id']]);
                    } catch (Exception $e) {
                        Craft::error($e->getMessage(), __METHOD__);
                    }
                }
            }
        }
    }
}
