<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Helpers\json\Encoder;

/**
 * @var array $endpoints Массив эндпойнтов для отправки запросов из JS
 */
?>
<div class="clear-module-widget" data-endpoints='<?= Encoder::encode($endpoints) ?>'>
    <div class="loading-indicator">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
    </div>
</div>
