<?php

namespace lsst\cantodamassets\controllers;

use Craft;
use craft\web\Controller;
use lsst\cantodamassets\CantoDamAssets;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Sync controller
 */
class SyncController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    public function beforeAction($action): bool
    {
        // Make sure the `secure_token` is present, and matches what is in the settings model
        $secureToken = $this->request->getRequiredBodyParam('secure_token');
        if ($secureToken !== CantoDamAssets::$plugin->getSettings()->getWebhookSecureToken()) {
            return false;
        }

        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * _canto-dam-assets/webhook action
     */
    public function actionIndex(): ?Response
    {
        return null;
    }

    /**
     * _canto-dam-assets/webhook/update-by-canto-id action
     * This action will be called by the Canto "Update Metadata" webhook when a Canto Asset's metadata is changed,
     * so that the asset metadata can be updated in all Canto DAM Assets field types
     *
     * @return Response|null
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionUpdateByCantoId(): ?Response
    {
        $cantoId = $this->request->getRequiredBodyParam('id');
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoApi = CantoDamAssets::$plugin->getApi();
        $cantoFieldData = $cantoApi->fetchFieldDataByCantoId($cantoId);
        if ($cantoFieldData) {
            $cantoAssets->updateByCantoId($cantoId, $cantoFieldData);

            return $this->asSuccess(message: Craft::t('_canto-dam-assets', 'Canto DAM Asset updated'), data: [
                'id' => $cantoId,
            ]);
        }

        return $this->asFailure(message: Craft::t('_canto-dam-assets', 'Canto DAM Asset update failed'), data: [
            'id' => $cantoId,
        ]);
    }

    /**
     * _canto-dam-assets/webhook/update-by-album-id action
     * This action will be called by the the following Canto webhooks, so Entire Album fields can be synced:
     * "Assign to Album", "Remove from Album", "Update Album"
     *
     * @return Response|null
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionUpdateByAlbumId(): ?Response
    {
        $albumId = $this->request->getRequiredBodyParam('album');
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoApi = CantoDamAssets::$plugin->getApi();
        $cantoFieldData = $cantoApi->fetchFieldDataByAlbumId($albumId);
        if ($cantoFieldData) {
            $cantoAssets->updateByAlbumId($albumId, $cantoFieldData);

            return $this->asSuccess(message: Craft::t('_canto-dam-assets', 'Canto DAM Album updated'), data: [
                'album' => $albumId,
            ]);
        }

        return $this->asFailure(message: Craft::t('_canto-dam-assets', 'Canto DAM Album update failed'), data: [
            'album' => $albumId,
        ]);
    }

    /**
     *  _canto-dam-assets/webhook/delete-by-canto-id action
     *  This action will be called by the Canto "Delete Asset" webhook when a Canto Asset is deleted,
     *  so that the asset can be deleted from all Canto DAM Assets field types
     *
     * @return Response|null
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionDeleteByCantoId(): ?Response
    {
        $cantoId = $this->request->getRequiredBodyParam('id');
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoAssets->deleteByCantoId($cantoId);

        return $this->asSuccess(message: Craft::t('_canto-dam-assets', 'Canto DAM Asset deleted'), data: [
            'id' => $cantoId,
        ]);
    }

    /**
     * _canto-dam-assets/webhook/update-by-album-id action
     * This action will be called by the the following Canto webhooks, so Entire Album fields can be synced:
     * "Assign to Album", "Remove from Album", "Update Album"
     *
     * @return Response|null
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionDeleteByAlbumId(): ?Response
    {
        $albumId = $this->request->getRequiredBodyParam('album');
        $cantoAssets = CantoDamAssets::$plugin->getAssets();
        $cantoAssets->deleteByAlbumId($albumId);

        return $this->asSuccess(message: Craft::t('_canto-dam-assets', 'Canto DAM Album deleted'), data: [
            'album' => $albumId,
        ]);
    }
}
