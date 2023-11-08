<?php

namespace lsst\cantodamassets\migrations;

use Craft;
use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Table;
use craft\fields\Matrix;
use craft\helpers\ElementHelper;
use craft\services\Matrix as MatrixService;
use lsst\cantodamassets\fields\CantoDamAsset;
use verbb\supertable\fields\SuperTableField;
use verbb\supertable\SuperTable;
use yii\base\Component;
use yii\db\Schema;

/**
 * m231108_024521_change_to_json_column migration.
 */
class m231108_024521_change_to_json_column extends Migration
{

    private const CONTENT_COLUMN_KEYS = [
        'cantoAssetData',
        'cantoAlbumData',
    ];

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Content columns
        $fields = Craft::$app->getFields()->getFieldsByType(CantoDamAsset::class);
        $this->changeToJsonColumn(Table::CONTENT, $fields);
        // Matrix columns
        $this->changeBlockTypeToJsonColumn(Matrix::class, Craft::$app->getMatrix());
        // SuperTable columns
        if (Craft::$app->getPlugins()->getPlugin('super-table')) {
            $this->changeBlockTypeToJsonColumn(SuperTableField::class, SuperTable::$plugin->getService());
        }
        // Neo columns
        /**
         * Neo field data is just stored in the content table via the Block element, so they'll already have been
         * taken care of by the // Content columns code above
         */

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m231108_024521_change_to_json_column cannot be reverted.\n";
        return false;
    }

    /**
     * Change the block type (Matrix, SuperTable, etc.) columns to JSON
     *
     * @param string $fieldType
     * @param Component $service
     * @return void
     */
    private function changeBlockTypeToJsonColumn(string $fieldType, Component $service): void
    {
        $blockFields = Craft::$app->getFields()->getFieldsByType($fieldType);
        foreach ($blockFields as $blockField) {
            // All block types & services have the same methods as Matrix
            /* @var Matrix $blockField */
            /* @var MatrixService $service */
            $table = $service->defineContentTableName($blockField);
            $blockTypes = $service->getBlockTypesByFieldId($blockField->id);
            foreach ($blockTypes as $blockType) {
                $fieldColumnPrefix = 'field_' . $blockType->handle . '_';
                $fields = $blockType->getCustomFields();
                $this->changeToJsonColumn($table, $fields, $fieldColumnPrefix);
            }
        }
    }

    /**
     * Update the $fields' column in $table, using the $fieldColumnPrefix if specified
     *
     * @param string $table
     * @param FieldInterface[] $fields
     * @param string|null $fieldColumnPrefix
     * @return void
     */
    private function changeToJsonColumn(string $table, array $fields, ?string $fieldColumnPrefix = null): void
    {
        foreach ($fields as $field) {
            foreach (self::CONTENT_COLUMN_KEYS as $columnKey) {
                $column = ElementHelper::fieldColumnFromField($field, $columnKey);
                if ($fieldColumnPrefix) {
                    $fieldPrefix = 'field_';
                    $pos = strpos($column, $fieldPrefix);
                    if ($pos !== false) {
                        $column = substr_replace($column, $fieldColumnPrefix, $pos, strlen($fieldPrefix));
                    }
                }
                $this->alterColumn($table, $column, Schema::TYPE_JSON);
            }
        }
    }
}
