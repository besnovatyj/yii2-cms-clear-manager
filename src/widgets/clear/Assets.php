<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\widgets\clear;

use yii\web\AssetBundle;

/**
 * Asset bundle для виджета очистки
 */
class Assets extends AssetBundle
{
    public $sourcePath = '@Besnovatyj/ClearManager/widgets/clear/media';

    public $css = [
        'css/clear.css',
    ];

    public $js = [
        'js/dist/index.iife.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
