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
use lsst\cantodamassets\models\CantoFieldData;
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
        return [
            'cantoId' => Schema::TYPE_STRING,
            'cantoAssetData' => Schema::TYPE_TEXT,
        ];
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): array
    {
        return [
            'cantoId' => $value['cantoId'] ?? null,
            'cantoAssetData' => $value['cantoAssetData'] ?? null,
        ];
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        if ($value === null) {
            $value = new CantoFieldData();
        }
        if (is_array($value)) {
            $value['cantoAssetData'] = Json::decodeIfJson($value['cantoAssetData']);
            $value = new CantoFieldData($value);
        }
        return $value;
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').CantoDamConnector(" . $jsonVars . ");");
        // In case we want to try to transform this image
        $previewUrl = $value->cantoAssetData[0]['previewUri'] ?? null;
        // The name to subtitle the preview
        $assetCount = count($value->cantoAssetData);
        $previewName = $value->cantoId == 0 ? "{$assetCount} images" : $value->cantoAssetData[0]['name'] ?? null;
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
                'previewUrl' => $previewUrl,
                'previewName' => $previewName,
                'assetCount' => $assetCount,
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

    /**
     * @inerhitDoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        // By default this method will allow searching on the primary content column for this field type,
        // which is `cantoId`, but this stub method is left in place in case we need to do some other kind
        // of custom searching in the future
        parent::modifyElementsQuery($query, $value);
    }
}
