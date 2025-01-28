<?php

namespace lsst\cantodamassets\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use GraphQL\Type\Definition\Type;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\gql\arguments\CantoDamAssetField;
use lsst\cantodamassets\gql\interfaces\CantoDamAssetInterface;
use lsst\cantodamassets\gql\resolvers\CantoDamAssetResolver;
use lsst\cantodamassets\models\CantoFieldData;
use yii\db\Schema;

/**
 * Canto Dam Asset field type
 */
class CantoDamAsset extends Field implements PreviewableFieldInterface
{
    protected const PICKER_TYPE_CLASS_MAP = [
        'singleImagePicker' => 'can-select-single',
        'multipleImagePicker' => 'can-select-single can-select-multiple',
        'wholeAlbumPicker' => 'can-select-album',
    ];

    public ?string $cantoAssetPickerType = 'singleImagePicker';

    public static function displayName(): string
    {
        return Craft::t('_canto-dam-assets', 'Canto Dam Asset');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public function getContentGqlType(): Type|array
    {
        return [
            'name' => $this->handle,
            'description' => 'Canto Dam Asset field',
            'args' => CantoDamAssetField::getArguments(),
            'type' => Type::listOf(CantoDamAssetInterface::getType()),
            'resolve' => CantoDamAssetResolver::class . '::resolve',
        ];
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('_canto-dam-assets/_components/fieldtypes/CantoDamAsset_settings.twig',
            [
                'field' => $this,
            ]);
    }

    public function getStaticHtml(mixed $value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        // Render the canto image template
        $twigVariables = $this->getFieldRenderVariables($value, $element, false);

        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/includes/CantoDamAsset_image.twig',
            $twigVariables
        );
    }

    public function getContentColumnType(): array|string
    {
        return [
            'cantoId' => Schema::TYPE_STRING,
            'cantoAlbumId' => Schema::TYPE_STRING,
            'cantoAssetData' => Schema::TYPE_JSON,
            'cantoAlbumData' => Schema::TYPE_JSON,
        ];
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): array
    {
        /** @var ?CantoFieldData $value */
        return [
            'cantoId' => $value->cantoId ?? null,
            'cantoAlbumId' => $value->cantoAlbumId ?? null,
            'cantoAssetData' => $value->cantoAssetData ?? null,
            'cantoAlbumData' => $value->cantoAlbumData ?? null,
        ];
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        $config = $value ?? [];
        if (is_array($config)) {
            // We are doing this twice to work around a Craft bug for now:
            // https://github.com/craftcms/cms/issues/13916
            $config['cantoAssetData'] = Json::decodeIfJson($config['cantoAssetData'] ?? []);
            $config['cantoAssetData'] = Json::decodeIfJson($config['cantoAssetData'] ?? []);
            $config['cantoAlbumData'] = Json::decodeIfJson($config['cantoAlbumData'] ?? []);
            $config['cantoAlbumData'] = Json::decodeIfJson($config['cantoAlbumData'] ?? []);
            return new CantoFieldData($config);
        }

        return $value;
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        // Render the canto image template
        $twigVariables = $this->getFieldRenderVariables($value, $element, false);

        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/includes/CantoDamAsset_image.twig',
            $twigVariables
        );
    }

    public function getElementValidationRules(): array
    {
        return [];
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

    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        /* @var CantoFieldData $value */
        $keywords = $value->cantoAssetData->flatten()->values()->filter()->all();
        return implode(' ', $keywords);
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $this->registerFieldJavaScript($value, $element);
        // Render the input template
        $twigVariables = $this->getFieldRenderVariables($value, $element, true);

        return $view->renderTemplate(
            '_canto-dam-assets/_components/fieldtypes/CantoDamAsset_input.twig',
            $twigVariables
        );
    }

    /**
     * Return the JavaScript JSON-encoded variables to pass down to our JavaScript jQuery plugin
     *
     * @param $value
     * @param ElementInterface $element
     */
    protected function registerFieldJavaScript($value, ElementInterface $element): void
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        $id = Html::id($this->handle);
        $namespace = $view->getNamespace();
        $namespacedId = $view->namespaceInputId($id);
        $jsonVars = Json::encode([
            'id' => $id,
            'fieldId' => $namespacedId,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Html::namespaceId('', $namespace),
            'appId' => CantoDamAssets::$plugin->getSettings()->getAppId(),
            'tenantHostName' => CantoDamAssets::$plugin->getSettings()->getTenantHostName(),
            'bodyClass' => self::PICKER_TYPE_CLASS_MAP[$this->cantoAssetPickerType] ?? self::PICKER_TYPE_CLASS_MAP['singleImagePicker'],
        ]);
        $view->registerJs(
            'if(jQuery().CantoDamConnector) {' .
            "$('#{$namespacedId}-field').CantoDamConnector(" . $jsonVars . ");" .
            '}' .
            'document.addEventListener("vite-script-loaded", function (e) {' .
            'if (e.detail.path === "src/js/canto-field.js") {' .
            "$('#{$namespacedId}-field').CantoDamConnector(" . $jsonVars . ");" .
            '}' .
            '});'
        );
    }

    /**
     * Return the Twig variables for rendering the field
     *
     * @param $value
     * @param ElementInterface $element
     * @param bool $enabled Whether the field is enabled or not
     * @return array[]
     */
    protected function getFieldRenderVariables($value, ElementInterface $element, bool $enabled): array
    {
        /** @var  CantoFieldData $value */
        $view = Craft::$app->getView();
        $id = Html::id($this->handle);
        $namespacedId = $view->namespaceInputId($id);
        // In case we want to try to transform this image
        $previewUrl = $value->cantoAssetData[0]['url']['directUrlPreview'] ?? null;
        // The name to subtitle the preview
        $assetCount = count($value->cantoAssetData);
        $previewName = $value->cantoId == 0 ? "{$assetCount} images" : $value->cantoAssetData[0]['name'] ?? null;
        $albumName = $value->cantoAlbumData['name'] ?? '';

        return [
            'id' => $id,
            'name' => $this->handle,
            'value' => $value,
            'fieldId' => $namespacedId,
            'elementId' => $element->id ?? null,
            'element' => Json::encode($element),
            'namespacedId' => $view->namespaceInputId($id),
            'accessToken' => CantoDamAssets::$plugin->getApi()->getAuthToken(),
            'config' => [
                'id' => $id,
                'enabled' => $enabled,
                'previewUrl' => $previewUrl,
                'previewName' => $previewName,
                'albumName' => $albumName,
                'assetCount' => $assetCount,
            ],
        ];
    }
}
