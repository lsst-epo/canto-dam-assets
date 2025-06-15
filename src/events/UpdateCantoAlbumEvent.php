<?php

namespace lsst\cantodamassets\events;

use yii\base\ModelEvent;

class UpdateCantoAlbumEvent extends ModelEvent
{
    public ?string $cantoAlbumId = null;
}
