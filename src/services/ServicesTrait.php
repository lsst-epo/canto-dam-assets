<?php

namespace lsst\cantodamassets\services;

use lsst\cantodamassets\web\assets\CantoDamAsset;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @property-read Assets $assets
 * @property VitePluginService $vite
 */
trait ServicesTrait
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        // Constants aren't allowed in traits until PHP >= 8.2, and config() is called before __construct(),
        // so we can't extract it from the passed in $config
        $majorVersion = '4';
        // Dev server container name & port are based on the major version of this plugin
        $devPort = 3000 + (int)$majorVersion;
        $versionName = 'v' . $majorVersion;
        return [
            'components' => [
                'api' => Api::class,
                'assets' => Assets::class,
                // Register the vite service
                'vite' => [
                    'assetClass' => CantoDamAsset::class,
                    'checkDevServer' => true,
                    'class' => VitePluginService::class,
                    'devServerInternal' => 'http://canto-dam-assets-' . $versionName . '-buildchain-dev:' . $devPort,
                    'devServerPublic' => 'http://localhost:' . $devPort,
                    'errorEntry' => 'src/js/app.ts',
                    'useDevServer' => true,
                ],
            ]
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the Api service
     *
     * @return Api The events service
     * @throws InvalidConfigException
     */
    public function getApi(): Api
    {
        return $this->get('api');
    }

    /**
     * Returns the Assets service
     *
     * @return Assets The events service
     * @throws InvalidConfigException
     */
    public function getAssets(): Assets
    {
        return $this->get('assets');
    }

    /**
     * Returns the vite service
     *
     * @return VitePluginService The vite service
     * @throws InvalidConfigException
     */
    public function getVite(): VitePluginService
    {
        return $this->get('vite');
    }
}
