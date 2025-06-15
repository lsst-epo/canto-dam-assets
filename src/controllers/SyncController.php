<?php

namespace lsst\cantodamassets\controllers;

use Craft;
use craft\web\Controller;
use lsst\cantodamassets\CantoDamAssets;
use lsst\cantodamassets\events\DeleteCantoAlbumEvent;
use lsst\cantodamassets\events\DeleteCantoAssetEvent;
use lsst\cantodamassets\events\UpdateCantoAlbumEvent;
use lsst\cantodamassets\events\UpdateCantoAssetEvent;
use lsst\cantodamassets\jobs\DeleteByAlbumId;
use lsst\cantodamassets\jobs\DeleteByCantoId;
use lsst\cantodamassets\jobs\UpdateByAlbumId;
use lsst\cantodamassets\jobs\UpdateByCantoId;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Sync controller
 */
class SyncController extends Controller
{
    /**
     * @event UpdateCantoAssetEvent The event that is triggered when a singular Canto asset is
     * updated via webhook from Canto.
     *
     * ```php
     * use lsst\cantodamassets\controllers\SyncController;
     * use lsst\cantodamassets\events\UpdateCantoAssetEvent;
     *
     * Event::on(SyncController::class,
     *     SyncController::EVENT_UPDATE_CANTO_ASSET,
     *     function(UpdateCantoAssetEvent $event) {
     *         // look at $event->cantoId;
     *     }
     * );
     * ```
     */
    public const EVENT_UPDATE_CANTO_ASSET = 'updateCantoAsset';

    /**
     * @event UpdateCantoAlbumEvent The event that is triggered when a Canto album is
     * updated via webhook from Canto.
     *
     * ```php
     * use lsst\cantodamassets\controllers\SyncController;
     * use lsst\cantodamassets\events\UpdateCantoAlbumEvent;
     *
     * Event::on(SyncController::class,
     *     SyncController::EVENT_UPDATE_CANTO_ALBUM,
     *     function(UpdateCantoAlbumEvent $event) {
     *         // look at $event->cantoAlbumId;
     *     }
     * );
     * ```
     */
    public const EVENT_UPDATE_CANTO_ALBUM = 'updateCantoAlbum';

    /**
     * @event DeleteCantoAssetEvent The event that is triggered when a singular Canto asset is
     * deleted via webhook from Canto.
     *
     * ```php
     * use lsst\cantodamassets\controllers\SyncController;
     * use lsst\cantodamassets\events\DeleteCantoAssetEvent;
     *
     * Event::on(SyncController::class,
     *     SyncController::EVENT_DELETE_CANTO_ASSET,
     *     function(DeleteCantoAssetEvent $event) {
     *         // look at $event->cantoId;
     *     }
     * );
     * ```
     */
    public const EVENT_DELETE_CANTO_ASSET = 'deleteCantoAsset';

    /**
     * @event DeleteCantoAlbumEvent The event that is triggered when a Canto album is
     * deleted via webhook from Canto.
     *
     * ```php
     * use lsst\cantodamassets\controllers\SyncController;
     * use lsst\cantodamassets\events\DeleteCantoAlbumEvent;
     *
     * Event::on(SyncController::class,
     *     SyncController::EVENT_DELETE_CANTO_ALBUM,
     *     function(DeleteCantoAlbumEvent $event) {
     *         // look at $event->cantoAlbumId;
     *     }
     * );
     * ```
     */
    public const EVENT_DELETE_CANTO_ALBUM = 'deleteCantoAlbum';

    public $defaultAction = 'index';
    public $enableCsrfValidation = false;
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
        // Throw the SyncController::EVENT_UPDATE_CANTO_ASSET event
        if ($this->hasEventHandlers(self::EVENT_UPDATE_CANTO_ASSET)) {
            $event = new UpdateCantoAssetEvent([
                'cantoId' => $cantoId,
            ]);
            $this->trigger(self::EVENT_UPDATE_CANTO_ASSET, $event);
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * _canto-dam-assets/sync/update-by-album-id action
     * This action will be called by the following Canto webhooks, so Entire Album fields can be synced:
     * "Assign to Album", "Remove from Album", "Update Album"
     *
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionUpdateByAlbumId(): ?Response
    {
        $albumId = $this->request->getRequiredBodyParam('album');
        Craft::$app->getQueue()->push(new UpdateByAlbumId([
            'id' => $albumId,
        ]));
        // Throw the SyncController::EVENT_UPDATE_CANTO_ALBUM event
        if ($this->hasEventHandlers(self::EVENT_UPDATE_CANTO_ALBUM)) {
            $event = new UpdateCantoAlbumEvent([
                'cantoAlbumId' => $albumId,
            ]);
            $this->trigger(self::EVENT_UPDATE_CANTO_ALBUM, $event);
        }

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
        // Throw the SyncController::EVENT_UPDATE_CANTO_ASSET event
        if ($this->hasEventHandlers(self::EVENT_DELETE_CANTO_ASSET)) {
            $event = new DeleteCantoAssetEvent([
                'cantoId' => $cantoId,
            ]);
            $this->trigger(self::EVENT_DELETE_CANTO_ASSET, $event);
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * _canto-dam-assets/sync/delete-by-album-id action
     * This action will be called by the following Canto webhooks, so Entire Album fields can be synced:
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
        // Throw the SyncController::EVENT_DELETE_CANTO_ALBUM event
        if ($this->hasEventHandlers(self::EVENT_DELETE_CANTO_ALBUM)) {
            $event = new DeleteCantoAlbumEvent([
                'cantoAlbumId' => $albumId,
            ]);
            $this->trigger(self::EVENT_DELETE_CANTO_ALBUM, $event);
        }

        return $this->redirectToPostedUrl();
    }
}
