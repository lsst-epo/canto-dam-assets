<?php

namespace lsst\cantodamassets\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Canto Dam asset bundle
 */
class CantoDamAsset extends AssetBundle
{
    public $sourcePath = '@lsst/cantodamassets/web/assets/dist';
    public $depends = [
        CpAsset::class,
    ];
    public $js = [
    ];
    public $css = [
    ];
}
