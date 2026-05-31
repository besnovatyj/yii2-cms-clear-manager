<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager;

/**
 * Модуль управления и очистки временных данных
 *
 * Предоставляет централизованный функционал для сбора информации
 * о временных данных из различных модулей приложения и их очистки.
 */
class Module extends \common\components\module\BaseModule
{
    public const bool EDITABLE = true;

    /**
     * Возвращает конфигурацию меню для админ-панели
     *
     * @return array
     */
    public static function getAdminMenu(): array
    {
        return require __DIR__ . '/config/adminMenu.php';
    }

    /**
     * Возвращает основную конфигурацию модуля
     *
     * @return array
     */
    public static function getConfig(): array
    {
        return require __DIR__ . '/config/config.php';
    }

    /**
     * Возвращает настраиваемые опции модуля
     *
     * @return array
     */
    public static function getOptions(): array
    {
        return require __DIR__ . '/config/options.php';
    }

    /**
     * Возвращает зависимости модуля
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return require __DIR__ . '/config/dependencies.php';
    }
}
