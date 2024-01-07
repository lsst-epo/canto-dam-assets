<?php

namespace lsst\cantodamassets\jobs;

use craft\i18n\Translation;
use craft\queue\BaseJob;
use lsst\cantodamassets\CantoDamAssets;

/**
 * Delete By Canto Id queue job
 */
class DeleteByCantoId extends BaseJob
{
    public ?string $id = null;

    /**
     * @param $queue
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue): void
    {
        CantoDamAssets::$plugin->getAssets()->deleteByCantoId($this->id);
    }

    protected function defaultDescription(): ?string
    {
        return Translation::prep('_canto-dam-assets', 'Deleting Canto Asset id {id}', [
            'id' => $this->id
        ]);
    }
}
