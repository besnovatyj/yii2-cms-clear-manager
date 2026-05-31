<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\ClearManager\widgets\clear\ClearWidget;

/**
 * @var yii\web\View $this
 */

$this->title = 'Управление временными данными';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="clear-index">
    <?= ClearWidget::widget() ?>
</div>
