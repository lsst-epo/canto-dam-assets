<?php

namespace lsst\cantodamassets\jobs;

use craft\i18n\Translation;
use craft\queue\BaseJob;
use lsst\cantodamassets\CantoDamAssets;
use yii\base\InvalidConfigException;

/**
 * Delete By Album Id queue job
 */
class DeleteByAlbumId extends BaseJob
{
    public ?string $id = null;

    /**
     * @param $queue
     * @return void
     * @throws InvalidConfigException
     */
    public function execute($queue): void
    {
        CantoDamAssets::$plugin->getAssets()->deleteByAlbumId($this->id);
    }

    protected function defaultDescription(): ?string
    {
        return Translation::prep('_canto-dam-assets', 'Deleting Canto Album id {id}', [
            'id' => $this->id
        ]);
    }
}
