<?php

namespace lsst\cantodamassets\migrations;

use Craft;
use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Table;
use craft\fields\Matrix;
use craft\helpers\ElementHelper;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\lib\laravel\Collection;
use verbb\supertable\fields\SuperTableField;
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
        $this->changeBlockTypeToJsonColumn(Matrix::class);
        // SuperTable columns
        if (Craft::$app->getPlugins()->getPlugin('super-table')) {
            $this->changeBlockTypeToJsonColumn(SuperTableField::class);
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
     * Change the block type field (Matrix, SuperTable, etc.) columns to JSON
     *
     * @param string $fieldType
     * @return void
     */
    private function changeBlockTypeToJsonColumn(string $fieldType): void
    {
        $blockFields = Craft::$app->getFields()->getFieldsByType($fieldType);
        foreach ($blockFields as $blockField) {
            // Block types have the same methods as Matrix
            /* @var Matrix $blockField */
            $fields = $blockField->getBlockTypeFields();
            // Filter out any non-CantoDamAsset fields
            $fields = (new Collection($fields))->filter(fn($value) => $value instanceof CantoDamAsset)->toArray();
            $this->changeToJsonColumn($blockField->contentTable, $fields);
        }
    }

    /**
     * Update the $fields' column in $table
     *
     * @param string $table
     * @param FieldInterface[] $fields
     * @return void
     */
    private function changeToJsonColumn(string $table, array $fields): void
    {
        foreach ($fields as $field) {
            foreach (self::CONTENT_COLUMN_KEYS as $columnKey) {
                $column = ElementHelper::fieldColumnFromField($field, $columnKey);
                if (Craft::$app->getDb()->getIsMysql()) {
                    $this->alterColumn($table, $column, Schema::TYPE_JSON);
                }
                if (Craft::$app->getDb()->getIsPgsql()) {
                    $this->alterColumnUsingPgsql($table, $column, Schema::TYPE_JSON, '::JSON');
                }
            }
        }
    }

    /**
     * Postgres doesn't let you change from Schema::TYPE_TEXT to Schema::TYPE_JSON without an explicit USING
     * The command is raw SQL since Yii2 doesn't support "USING" with its db migration/command/builder
     * ref: https://echobind.com/post/safely-alter-postgres-columns-with-using
     *
     * @param $table
     * @param $column
     * @param $type
     * @param $using
     * @return void
     * @throws \yii\db\Exception
     */
    private function alterColumnUsingPgsql($table, $column, $type, $using): void
    {
        $time = $this->beginCommand("alter column $column in table $table to $type using $using");
        $cmd = $this->db->createCommand('ALTER TABLE ' . $table . ' ALTER COLUMN "' . $column . '" TYPE jsonb USING "' . $column . '"' . $using . ', ALTER COLUMN "' . $column . '" DROP DEFAULT, ALTER COLUMN "' . $column . '" DROP NOT NULL');
        $cmd->execute();
        $this->endCommand($time);
    }
}
