<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\controllers\backend;



/**
 * Контроллер главной страницы модуля Clear
 */
class IndexController extends \yii\web\Controller
{
    /**
     * Отображает главную страницу с виджетом очистки
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
