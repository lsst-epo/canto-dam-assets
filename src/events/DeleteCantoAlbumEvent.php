<?php

namespace lsst\cantodamassets\events;

use yii\base\ModelEvent;

class DeleteCantoAlbumEvent extends ModelEvent
{
    public ?string $cantoAlbumId = null;
}
