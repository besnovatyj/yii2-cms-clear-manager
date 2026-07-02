<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\controllers\backend;

use Besnovatyj\ClearManager\services\DirectoryClearService;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Контроллер для работы с данными очистки модуля Clear.
 *
 * Обработка ошибок делегирована фреймворку ({@see \yii\web\ErrorHandler}):
 *  - ожидаемые/операционные сбои бросаются типизированным {@see ServerErrorHttpException}
 *    (корректный HTTP-статус + сообщение, безопасное к показу);
 *  - непредвиденные исключения сервиса (файловая система, APCu) НЕ ловятся здесь и всплывают
 *    к ErrorHandler — в проде их детали скрыты, в debug видны.
 *
 * Ответ об ошибке приходит с реальным HTTP-статусом (4xx/5xx) в нативном JSON-формате Yii
 * (`{name, message, code, status, ...}`). Успех — конверт приложения `{status:'success', ...}`.
 */
class DataController extends Controller
{
    private DirectoryClearService $service;

    public function __construct($id, $module, DirectoryClearService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Все экшены отдают JSON и принимают только AJAX-запросы.
     *
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        // Формат ставим до parent::beforeAction — чтобы ошибки фильтров (verb) тоже ушли как JSON.
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->getRequest()->getIsAjax()) {
            throw new BadRequestHttpException('Ожидается AJAX-запрос.');
        }
        return true;
    }

    public function actionGetCache(): array
    {
        return ['status' => 'success', 'data' => $this->service->getCacheData()];
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionClearCache(): array
    {
        if (!$this->service->clearCache()) {
            throw new ServerErrorHttpException('Не удалось очистить кеш.');
        }
        return ['status' => 'success', 'message' => 'Кеш успешно очищен'];
    }

    /**
     * @throws Exception
     */
    public function actionGetFrontAssets(): array
    {
        return ['status' => 'success', 'data' => $this->service->getFrontAssetsData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearFrontAssets(): array
    {
        if (!$this->service->clearFrontAssets()) {
            throw new ServerErrorHttpException('Не удалось очистить ресурсы фронтэнда.');
        }
        return ['status' => 'success', 'message' => 'Ресурсы фронтэнда очищены'];
    }

    /**
     * @throws Exception
     */
    public function actionGetBackAssets(): array
    {
        return ['status' => 'success', 'data' => $this->service->getBackAssetsData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearBackAssets(): array
    {
        if (!$this->service->clearBackAssets()) {
            throw new ServerErrorHttpException('Не удалось очистить ресурсы бэкэнда.');
        }
        return ['status' => 'success', 'message' => 'Ресурсы бэкэнда очищены'];
    }

    /**
     * @throws Exception
     */
    public function actionGetLogs(): array
    {
        return ['status' => 'success', 'data' => $this->service->getLogsData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearLogs(): array
    {
        if (!$this->service->clearLogs()) {
            throw new ServerErrorHttpException('Не удалось очистить логи.');
        }
        return ['status' => 'success', 'message' => 'Логи очищены'];
    }

    /**
     * @throws Exception
     */
    public function actionGetDebug(): array
    {
        return ['status' => 'success', 'data' => $this->service->getDebugData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearDebug(): array
    {
        if (!$this->service->clearDebug()) {
            throw new ServerErrorHttpException('Не удалось очистить debug панель.');
        }
        return ['status' => 'success', 'message' => 'Debug панель очищена'];
    }

    /**
     * @throws Exception
     */
    public function actionGetMail(): array
    {
        return ['status' => 'success', 'data' => $this->service->getMailData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearMail(): array
    {
        if (!$this->service->clearMail()) {
            throw new ServerErrorHttpException('Не удалось очистить отладочные письма.');
        }
        return ['status' => 'success', 'message' => 'Отладочные письма очищены'];
    }

    /**
     * @throws Exception
     */
    public function actionGetStatic(): array
    {
        return ['status' => 'success', 'data' => $this->service->getStaticData()];
    }

    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionClearStatic(): array
    {
        if (!$this->service->clearStatic()) {
            throw new ServerErrorHttpException('Не удалось очистить кеш статики.');
        }
        return ['status' => 'success', 'message' => 'Кеш статики очищен'];
    }
}
