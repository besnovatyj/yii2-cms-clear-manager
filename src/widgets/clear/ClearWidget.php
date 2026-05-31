<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\widgets\clear;

use Besnovatyj\ClearManager\services\EndpointCollectorService;
use modules\user\components\Helper;
use yii\bootstrap5\Widget;

/**
 * Виджет для отображения и управления очисткой временных данных
 */
class ClearWidget extends Widget
{
    /**
     * @inheritDoc
     */
    public function run(): string
    {
        Assets::register($this->view);

        $collectorService = new EndpointCollectorService();
        $endpoints = $collectorService->collectEndpoints();
        $validatedEndpoints = $collectorService->validateEndpoints($endpoints);

        // Фильтруем эндпойнты по правам доступа
        $filteredEndpoints = $this->filterEndpointsByAccess($validatedEndpoints);

        return $this->render('index', [
            'endpoints' => $filteredEndpoints,
        ]);
    }

    /**
     * Фильтрует эндпойнты по правам доступа пользователя
     *
     * @param array $endpoints Массив эндпойнтов
     * @return array Отфильтрованный массив
     */
    private function filterEndpointsByAccess(array $endpoints): array
    {
        $filtered = [];

        foreach ($endpoints as $moduleId => $moduleEndpoints) {
            $filteredModuleEndpoints = $this->filterModuleEndpoints($moduleEndpoints);

            if (!empty($filteredModuleEndpoints)) {
                $filtered[$moduleId] = $filteredModuleEndpoints;
            }
        }

        return $filtered;
    }

    /**
     * Фильтрует эндпойнты модуля
     *
     * @param array $moduleEndpoints Эндпойнты модуля
     * @return array
     */
    private function filterModuleEndpoints(array $moduleEndpoints): array
    {
        // Проверяем, один эндпойнт или набор
        if (isset($moduleEndpoints['getData']) && isset($moduleEndpoints['clear'])) {
            // Один эндпойнт
            if ($this->hasAccess($moduleEndpoints['getData']) && $this->hasAccess($moduleEndpoints['clear'])) {
                return $moduleEndpoints;
            }
            return [];
        }

        // Набор эндпойнтов
        $filtered = [];
        foreach ($moduleEndpoints as $key => $endpoint) {
            if ($this->hasAccess($endpoint['getData']) && $this->hasAccess($endpoint['clear'])) {
                $filtered[$key] = $endpoint;
            }
        }

        return $filtered;
    }

    /**
     * Проверяет доступ к маршруту
     *
     * @param string $route Маршрут
     * @return bool
     */
    private function hasAccess(string $route): bool
    {
        return Helper::checkRoute($route);
    }
}
