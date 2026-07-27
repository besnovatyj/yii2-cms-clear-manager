<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\widgets\dashboard;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * Плитка дашборда: кнопка сброса кеша приложения.
 *
 * Бьёт AJAX-POST в существующий эндпойнт модуля `/ClearManager/backend/data/clear-cache`. JS —
 * самодостаточный инлайн (fetch) в позиции POS_END: без внешних ассетов и без зависимости от jQuery,
 * что согласуется с тем, что ассеты в админке подключает пользователь сам. Рендерит только тело
 * карточки — «каркас» рисует модуль дашборда.
 */
class ClearCacheTile extends Widget
{
    public function run(): string
    {
        $buttonId = 'dash-clear-cache-' . $this->getId();
        $endpoint = Json::encode(Url::to(['/ClearManager/backend/data/clear-cache']));
        $csrfHeader = Json::encode(Yii::$app->request->csrfHeader);
        $csrfToken = Json::encode(Yii::$app->request->getCsrfToken());

        $this->view->registerJs(
            <<<JS
            (function () {
                var btn = document.getElementById('{$buttonId}');
                if (!btn) { return; }
                var status = btn.parentNode.querySelector('[data-clear-status]');
                btn.addEventListener('click', function () {
                    btn.disabled = true;
                    status.textContent = 'Очистка…';
                    var headers = { 'X-Requested-With': 'XMLHttpRequest' };
                    headers[{$csrfHeader}] = {$csrfToken};
                    fetch({$endpoint}, { method: 'POST', headers: headers })
                        .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
                        .then(function () { status.textContent = 'Кеш очищен'; })
                        .catch(function () { status.textContent = 'Ошибка очистки'; })
                        .finally(function () { btn.disabled = false; });
                });
            })();
            JS,
            View::POS_END
        );

        $hint = Html::tag('div', 'Сброс кеша приложения (apcu).', ['class' => 'text-muted small mb-2']);
        $button = Html::button('<i class="bi bi-trash me-1"></i>Очистить кеш', [
            'id' => $buttonId,
            'type' => 'button',
            'class' => 'btn btn-sm btn-outline-danger',
        ]);
        $status = Html::tag('span', '', ['data-clear-status' => true, 'class' => 'text-muted small ms-2']);

        return $hint . Html::tag('div', $button . $status, ['class' => 'd-flex align-items-center']);
    }
}
