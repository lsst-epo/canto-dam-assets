<?php

namespace lsst\cantodamassets\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use lsst\cantodamassets\CantoDamAssets;
use yii\db\Schema;

/**
 * Canto Dam Asset field type
 */
class CantoDamAsset extends Field implements PreviewableFieldInterface
{
    public static function displayName(): string
    {
        return Craft::t('_canto-dam-assets', 'Canto Dam Asset');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_STRING;
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        return $value;
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $id = Html::id($this->handle);
        // Render the input template
        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/CantoDamAsset_input.twig',
            [
                'name' => $this->handle,
                'value' => $value,
                'fieldId' => $this->id,
                'elementId' => $element->id ?? null,
                'id' => $id,
                'element' => Json::encode($element),
                'namespacedId' => $view->namespaceInputId($id),
                'accessToken' => CantoDamAssets::$plugin->assets->getAuthToken()
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        return [];
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        return StringHelper::toString($value, ' ');
    }

    public function getElementConditionRuleType(): array|string|null
    {
        return null;
    }

    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        parent::modifyElementsQuery($query, $value);
    }
}
