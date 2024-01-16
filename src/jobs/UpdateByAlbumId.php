<?php

namespace lsst\cantodamassets\jobs;

use craft\i18n\Translation;
use craft\queue\BaseJob;
use lsst\cantodamassets\CantoDamAssets;
use yii\base\InvalidConfigException;

/**
 * Update By Album Id queue job
 */
class UpdateByAlbumId extends BaseJob
{
    public ?string $id = null;

    /**
     * @param $queue
     * @return void
     * @throws InvalidConfigException
     */
    public function execute($queue): void
    {
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoApi = CantoDamAssets::$plugin->getApi();
        $cantoFieldData = $cantoApi->fetchFieldDataByAlbumId($this->id);
        if ($cantoFieldData) {
            $cantoAssets->updateByAlbumId($this->id, $cantoFieldData);
        }
    }

    protected function defaultDescription(): ?string
    {
        return Translation::prep('_canto-dam-assets', 'Updating Canto Album id {id}', [
            'id' => $this->id
        ]);
    }
}
