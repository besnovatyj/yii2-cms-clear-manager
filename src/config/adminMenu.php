<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [[
    'label' => 'Clear Manager',
    'iconClass' => 'bi bi-trash me-1',
    'url' => ['/ClearManager/backend/index/index'],
    'active' => static function () {
        return str_contains(\Yii::$app->request->url, 'ClearManager/backend');
    },
    '_meta' => [
        'placements' => [
            [
                'location' => 'right-sidebar',
                'group' => 'Service',
                'groupIcon' => 'bi bi-sliders',
                'priority' => 100,
                'groupPriority' => 100,
            ],
        ],
    ],
]];
