<?php

namespace lsst\cantodamassets\services;

use Craft;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\db\Table;
use craft\fields\Matrix;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\lib\laravel\Collection;
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
        $this->updateBlockTypeContent(Matrix::class, $value, $cantoFieldData, $columnKey);
        if (Craft::$app->getPlugins()->getPlugin('super-table')) {
            $this->updateBlockTypeContent(SuperTableField::class, $value, $cantoFieldData, $columnKey);
        }
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
        $this->updateBlockTypeContent(Matrix::class, $value, $cantoFieldData, $columnKey);
        if (Craft::$app->getPlugins()->getPlugin('super-table')) {
            $this->updateBlockTypeContent(SuperTableField::class, $value, $cantoFieldData, $columnKey);
        }
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
        $fields = Craft::$app->getFields()->getFieldsByType(CantoDamAsset::class);
        $this->updateContent($value, $cantoFieldData, $columnKey, $fields, Table::CONTENT);
    }

    /**
     * Update $fieldType block type content in its table where the $columnKey matches $value with the $cantoFieldData
     *
     * @param string $fieldType
     * @param string $value
     * @param CantoFieldData $cantoFieldData
     * @param string|null $columnKey
     * @return void
     */
    protected function updateBlockTypeContent(string $fieldType, string $value, CantoFieldData $cantoFieldData, ?string $columnKey): void
    {
        $blockFields = $this->getBlockFields($fieldType);
        foreach ($blockFields as $blockField) {
            $contentTableName = $blockField->contentTable;
            $fields = $blockField->getBlockTypeFields();
            // Filter out any non-CantoDamAsset fields
            $fields = (new Collection($fields))->filter(fn($value) => $value instanceof CantoDamAsset)->toArray();
            $this->updateContent($value, $cantoFieldData, $columnKey, $fields, $contentTableName);
        }
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
        $columnKey = self::CONTENT_COLUMN_KEY_MAPPINGS[$columnKey] ?? null;
        foreach ($cantoDamAssetFields as $cantoDamAssetField) {
            // Find any $queryColumn content column row that match $value, and update them with the data from $cantoFieldData
            $queryColumn = ElementHelper::fieldColumnFromField($cantoDamAssetField, $columnKey);
            if ($queryColumn) {
                $columns = $this->getColumns($cantoDamAssetField, $cantoFieldData);
                try {
                    $rows = Db::update($table, $columns, [$queryColumn => $value]);
                } catch (Exception $e) {
                    Craft::error($e->getMessage(), __METHOD__);
                }
            }
            // If the column we're updating is the `cantoId`, we need to search the JSON contents of `cantoAssetData`
            // in order to update any canto assets contained within the JSON blobs as well
            if ($columnKey === 'cantoId') {
                $db = Craft::$app->getDb();
                // Get any existing Canto Assets fields that contain the asset ID we're updating
                $cantoIdFieldName = ElementHelper::fieldColumnFromField($cantoDamAssetField, self::CONTENT_COLUMN_KEY_MAPPINGS['cantoId']);
                $cantoAssetDataFieldName = ElementHelper::fieldColumnFromField($cantoDamAssetField, self::CONTENT_COLUMN_KEY_MAPPINGS['cantoAssetData']);
                $jsonSearchNeedle = ['id' => $cantoFieldData->cantoId];
                $jsonSearchSql = '';
                if ($db->getIsMysql()) {
                    $jsonSearchSql = $this->mySqlJsonContains($cantoAssetDataFieldName, $jsonSearchNeedle);
                }
                if ($db->getIsPgsql()) {
                    $jsonSearchSql = $this->pgSqlJsonContains($cantoAssetDataFieldName, $jsonSearchNeedle);
                }
                $rows = (new Query())
                    ->select([$cantoAssetDataFieldName])
                    ->from([$table])
                    ->where([$cantoIdFieldName => 0, $cantoAssetDataFieldName => $jsonSearchSql])
                    ->all();
            }
        }
    }

    /**
     * Return a jsonContains expression properly formatted for MySQL
     *
     * @param string $targetSql
     * @param mixed $value
     * @return string
     */
    private function mySqlJsonContains(string $targetSql, mixed $value): string
    {
        $value = Craft::$app->getDb()->quoteValue(Json::encode($value));
        return "JSON_CONTAINS($targetSql, $value)";
    }

    /**
     * Return a jsonContains expression properly formatted for Postgres
     *
     * @param string $targetSql
     * @param mixed $value
     * @return string
     */
    private function pgSqlJsonContains(string $targetSql, mixed $value): string
    {
        $value = Craft::$app->getDb()->quoteValue(Json::encode($value));
        return "($targetSql @> $value::jsonb)";
    }

    /**
     * Block type fields  have the same methods as Matrix
     *
     * @param string $fieldType
     * @return Matrix[]
     */
    private function getBlockFields(string $fieldType): array
    {
        /** @phpstan-ignore-next-line */
        return Craft::$app->getFields()->getFieldsByType($fieldType);
    }

    /**
     * Return the $cantoDamAssetField db columns to update with the values from the  $cantoFieldData
     *
     * @param FieldInterface $cantoDamAssetField
     * @param CantoFieldData $cantoFieldData
     * @return array
     */
    private function getColumns(FieldInterface $cantoDamAssetField, CantoFieldData $cantoFieldData): array
    {
        $columns = [];
        foreach (self::CONTENT_COLUMN_KEY_MAPPINGS as $propertyName => $selectColumnKey) {
            $columns[ElementHelper::fieldColumnFromField($cantoDamAssetField, $selectColumnKey)] = $cantoFieldData->$propertyName;
        }

        return $columns;
    }
}
