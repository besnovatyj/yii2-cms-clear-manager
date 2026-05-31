<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\contracts;

/**
 * Интерфейс для формата данных, возвращаемых модулями
 */
interface ClearDataInterface
{
    /**
     * Возвращает отформатированную строку для отображения
     *
     * @return string
     */
    public function getFormattedString(): string;
}
