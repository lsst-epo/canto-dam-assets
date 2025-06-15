<?php

namespace lsst\cantodamassets\events;

use yii\base\ModelEvent;

class DeleteCantoAssetEvent extends ModelEvent
{
    public ?string $cantoId = null;
}
