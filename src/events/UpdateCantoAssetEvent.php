<?php

namespace lsst\cantodamassets\events;

use yii\base\ModelEvent;

class UpdateCantoAssetEvent extends ModelEvent
{
    public ?string $cantoId = null;
}
