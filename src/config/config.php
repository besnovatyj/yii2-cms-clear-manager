<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

/**
 * Конфигурация модуля ClearManager
 */
return [
    'id' => 'ClearManager',
    'params' => [
        'iconClass' => 'bi bi-trash',

        /**
         * Список путей к общим директориям для очистки
         */
        'clear-dirs' => [
            'frontAssets' => Yii::getAlias('@frontend/pub/assets'),
            'backAssets' => Yii::getAlias('@backend/pub/assets'),
            'logs' => Yii::getAlias('@runtime/logs'),
            'css' => Yii::getAlias('@runtime/CSS'),
            'debug' => Yii::getAlias('@runtime/debug'),
            'html' => Yii::getAlias('@runtime/HTML'),
            'mail' => Yii::getAlias('@runtime/mail'),
            'uri' => Yii::getAlias('@runtime/URI'),
            'static' => Yii::getAlias('@static/cache'),
        ],

        /**
         * Эндпойнты модуля для получения данных и очистки
         */
        'endpoints' => [
            'clear' => [
                'cache' => [
                    'rowTitle' => 'Кеш приложения',
                    'getData' => '/ClearManager/backend/data/get-cache',
                    'clear' => '/ClearManager/backend/data/clear-cache',
                ],
                'frontAssets' => [
                    'rowTitle' => 'Ресурсы фронтэнда',
                    'getData' => '/ClearManager/backend/data/get-front-assets',
                    'clear' => '/ClearManager/backend/data/clear-front-assets',
                ],
                'backAssets' => [
                    'rowTitle' => 'Ресурсы бэкэнда',
                    'getData' => '/ClearManager/backend/data/get-back-assets',
                    'clear' => '/ClearManager/backend/data/clear-back-assets',
                ],
                'logs' => [
                    'rowTitle' => 'Логи',
                    'getData' => '/ClearManager/backend/data/get-logs',
                    'clear' => '/ClearManager/backend/data/clear-logs',
                ],
                'debug' => [
                    'rowTitle' => 'Debug панель',
                    'getData' => '/ClearManager/backend/data/get-debug',
                    'clear' => '/ClearManager/backend/data/clear-debug',
                ],
                'mail' => [
                    'rowTitle' => 'Отладочные письма',
                    'getData' => '/ClearManager/backend/data/get-mail',
                    'clear' => '/ClearManager/backend/data/clear-mail',
                ],
                'static' => [
                    'rowTitle' => 'Кеш статики',
                    'getData' => '/ClearManager/backend/data/get-static',
                    'clear' => '/ClearManager/backend/data/clear-static',
                ],
            ],
        ],
    ],
];
