<?php

namespace lsst\cantodamassets\jobs;

use craft\i18n\Translation;
use craft\queue\BaseJob;
use lsst\cantodamassets\CantoDamAssets;

/**
 * Update By Canto Id queue job
 */
class UpdateByCantoId extends BaseJob
{
    public ?string $id = null;

    /**
     * @param $queue
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue): void
    {
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoApi = CantoDamAssets::$plugin->getApi();
        $cantoFieldData = $cantoApi->fetchFieldDataByCantoId($this->id);
        if ($cantoFieldData) {
            $cantoAssets->updateByCantoId($this->id, $cantoFieldData);
        }
    }

    protected function defaultDescription(): ?string
    {
        return Translation::prep('_canto-dam-assets', 'Updating Canto Asset id {id}', [
            'id' => $this->id
        ]);
    }
}
