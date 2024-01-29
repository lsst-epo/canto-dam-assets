<?php

namespace lsst\cantodamassets;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use lsst\cantodamassets\fields\CantoDamAsset;
use lsst\cantodamassets\models\Settings;
use lsst\cantodamassets\services\ServicesTrait;
use lsst\cantodamassets\variables\CantoVariable;
use yii\base\Event;

/**
 * Canto DAM Assets plugin
 *
 * @method static CantoDamAssets getInstance()
 * @method Settings getSettings()
 * @property-read Settings $settings
 */
class CantoDamAssets extends Plugin
{
    use ServicesTrait;

    public string $schemaVersion = '1.0.1';
    public bool $hasCpSettings = true;

    public static ?CantoDamAssets $plugin = null;

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = self::getInstance();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });
    }

    /**
     * @inheritDoc
     */
    protected function createSettingsModel(): ?Settings
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * @inheritDoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('_canto-dam-assets/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Attach our plugin's even handlers
     *
     * @return void
     */
    protected function attachEventHandlers(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('canto', [
                    'class' => CantoVariable::class,
                    'viteService' => $this->vite,
                ]);
            }
        );
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = CantoDamAsset::class;
            });
        // Add permission for Editors
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    "heading" => "Canto DAM Assets Picker Extraordinaire",
                    "permissions" => [
                        'accessPlugin-_canto-dam-assets' => [
                            'label' => 'Use Canto DAM Assets Fields',
                        ],
                    ],
                ];
            }
        );
    }
}
