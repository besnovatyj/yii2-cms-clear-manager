<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\dashboard\DashboardWidgetDescriptor;
use Besnovatyj\Contracts\dashboard\ProvidesDashboardWidgets;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesAdminMenu;
use Besnovatyj\ClearManager\widgets\dashboard\ClearCacheTile;

/**
 * Модуль управления и очистки временных данных
 *
 * Предоставляет централизованный функционал для сбора информации
 * о временных данных из различных модулей приложения и их очистки.
 */
class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu, ProvidesDashboardWidgets
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'ClearManager';

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function adminMenu(): array { return require __DIR__.'/config/adminMenu.php'; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }

    /** @return DashboardWidgetDescriptor[] */
    public static function dashboardWidgets(): array
    {
        return [
            new DashboardWidgetDescriptor(
                id: self::MODULE_ID . '.clearCache',
                title: 'Очистка кеша',
                tileClass: ClearCacheTile::class,
                iconClass: 'bi bi-trash',
                priority: 300,
            ),
        ];
    }

}
