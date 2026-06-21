<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\controllers\backend;


use Besnovatyj\ClearManager\services\DirectoryClearService;
use Exception;
use Yii;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Контроллер для работы с данными очистки модуля Clear
 */
class DataController extends  \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;
    private DirectoryClearService $service;

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
     * @inheritDoc
     */
    public function __construct($id, $module, DirectoryClearService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    /**
     * Получить данные о кеше
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetCache(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getCacheData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить кеш
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearCache(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearCache()) {
                    return [
                        'status' => 'success',
                        'message' => 'Кеш успешно очищен',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные о ресурсах фронтэнда
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetFrontAssets(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getFrontAssetsData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить ресурсы фронтэнда
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearFrontAssets(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearFrontAssets()) {
                    return [
                        'status' => 'success',
                        'message' => 'Ресурсы фронтэнда очищены',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные о ресурсах бэкэнда
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetBackAssets(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getBackAssetsData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить ресурсы бэкэнда
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearBackAssets(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearBackAssets()) {
                    return [
                        'status' => 'success',
                        'message' => 'Ресурсы бэкэнда очищены',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные о логах
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetLogs(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getLogsData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить логи
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearLogs(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearLogs()) {
                    return [
                        'status' => 'success',
                        'message' => 'Логи очищены',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные о debug панели
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetDebug(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getDebugData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить debug панель
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearDebug(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearDebug()) {
                    return [
                        'status' => 'success',
                        'message' => 'Debug панель очищена',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные об отладочных письмах
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetMail(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getMailData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить отладочные письма
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearMail(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearMail()) {
                    return [
                        'status' => 'success',
                        'message' => 'Отладочные письма очищены',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Получить данные о кеше статики
     *
     * @return array
     * @throws ExitException
     */
    public function actionGetStatic(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                return [
                    'status' => 'success',
                    'data' => $this->service->getStaticData(),
                ];
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }

    /**
     * Очистить кеш статики
     *
     * @return array
     * @throws ExitException
     */
    public function actionClearStatic(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['status' => 'error', 'message' => 'Отсутствует заголовок X-Requested-With'];

        if (Yii::$app->getRequest()->getIsAjax()) {
            try {
                if ($this->service->clearStatic()) {
                    return [
                        'status' => 'success',
                        'message' => 'Кеш статики очищен',
                    ];
                }
            } catch (Exception $e) {
                $this->ajaxError($e);
            }
        }

        return $response;
    }
}
