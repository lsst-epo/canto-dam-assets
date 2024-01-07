<?php

namespace lsst\cantodamassets\controllers;

use Craft;
use craft\web\Controller;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\jobs\DeleteByAlbumId;
use lsst\cantodamassets\jobs\DeleteByCantoId;
use lsst\cantodamassets\jobs\UpdateByCantoId;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Sync controller
 */
class SyncController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;
    public $enableCsrfValidation = false;

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
     * _canto-dam-assets/sync/update-by-canto-id action
     * This action will be called by the Canto "Update Metadata" webhook when a Canto Asset's metadata is changed,
     * so that the asset metadata can be updated in all Canto DAM Assets field types
     *
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionUpdateByCantoId(): ?Response
    {
        $cantoId = $this->request->getRequiredBodyParam('id');
        Craft::$app->getQueue()->push(new UpdateByCantoId([
            'id' => $cantoId,
        ]));

        return $this->redirectToPostedUrl();
    }

    /**
     * _canto-dam-assets/sync/update-by-album-id action
     * This action will be called by the the following Canto webhooks, so Entire Album fields can be synced:
     * "Assign to Album", "Remove from Album", "Update Album"
     *
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionUpdateByAlbumId(): ?Response
    {
        $albumId = $this->request->getRequiredBodyParam('album');
        Craft::$app->getQueue()->push(new UpdateByCantoId([
            'id' => $albumId,
        ]));

        return $this->redirectToPostedUrl();
    }

    /**
     *  _canto-dam-assets/sync/delete-by-canto-id action
     *  This action will be called by the Canto "Delete Asset" webhook when a Canto Asset is deleted,
     *  so that the asset can be deleted from all Canto DAM Assets field types
     *
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionDeleteByCantoId(): ?Response
    {
        $cantoId = $this->request->getRequiredBodyParam('id');
        Craft::$app->getQueue()->push(new DeleteByCantoId([
            'id' => $cantoId,
        ]));

        return $this->redirectToPostedUrl();
    }

    /**
     * _canto-dam-assets/sync/delete-by-album-id action
     * This action will be called by the the following Canto webhooks, so Entire Album fields can be synced:
     * "Assign to Album", "Remove from Album", "Update Album"
     *
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionDeleteByAlbumId(): ?Response
    {
        $albumId = $this->request->getRequiredBodyParam('album');
        Craft::$app->getQueue()->push(new DeleteByAlbumId([
            'id' => $albumId,
        ]));

        return $this->redirectToPostedUrl();
    }
}
