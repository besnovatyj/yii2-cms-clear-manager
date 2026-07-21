<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\services;

use Yii;
use yii\base\Module;

/**
 * Сервис для сбора эндпойнтов очистки из конфигураций модулей
 */
class EndpointCollectorService
{
    /**
     * Собирает все эндпойнты для получения данных и очистки из модулей
     *
     * @return array Массив эндпойнтов, индексированный ID модулей
     */
    public function collectEndpoints(): array
    {
        $endpoints = [];
        $modules = Yii::$app->getModules();

        foreach ($modules as $moduleId => $module) {
            $params = $this->extractModuleParams($module);
            if ($params === []) {
                continue;
            }

            $moduleEndpoints = $this->extractClearEndpoints($params);
            if (!empty($moduleEndpoints)) {
                $endpoints[$moduleId] = $moduleEndpoints;
            }
        }

        return $endpoints;
    }

    /**
     * Читает params модуля БЕЗ его инстанцирования.
     *
     * `getModules()` возвращает либо уже созданный экземпляр (тогда берём его `params`), либо
     * массив-дефиницию из конфига (тогда берём `params` прямо из него). Так сбор эндпойнтов не запускает
     * `init()`/бутстрап каждого зарегистрированного модуля. Строку/замыкание в качестве дефиниции не
     * разворачиваем (это потребовало бы инстанцирования) — такие модули пропускаем.
     *
     * @param mixed $module Экземпляр модуля или его дефиниция из конфига
     * @return array Массив params (пустой, если недоступен без инстанцирования)
     */
    private function extractModuleParams(mixed $module): array
    {
        if ($module instanceof Module) {
            return $module->params ?? [];
        }

        if (is_array($module)) {
            return $module['params'] ?? [];
        }

        return [];
    }

    /**
     * Извлекает эндпойнты очистки из params модуля
     *
     * @param array $params Params модуля
     * @return array
     */
    private function extractClearEndpoints(array $params): array
    {
        $endpoints = $params['endpoints']['clear'] ?? [];

        if (empty($endpoints)) {
            return [];
        }

        return $this->validateAndNormalizeEndpoints($endpoints);
    }

    /**
     * Валидирует и нормализует эндпойнты
     *
     * @param array $endpoints Массив эндпойнтов
     * @return array
     */
    private function validateAndNormalizeEndpoints(array $endpoints): array
    {
        // Проверяем, весь массив - это один эндпойнт или набор эндпойнтов
        if (isset($endpoints['getData']) && isset($endpoints['clear'])) {
            // Весь массив - это один эндпойнт (например, для модуля с одним набором данных)
            return $this->isValidEndpoint($endpoints) ? $endpoints : [];
        }

        // Это набор эндпойнтов, проходим по каждому
        $normalized = [];
        foreach ($endpoints as $key => $endpoint) {
            if (!is_array($endpoint)) {
                continue;
            }

            if ($this->isValidEndpoint($endpoint)) {
                $normalized[$key] = $endpoint;
            }
        }

        return $normalized;
    }

    /**
     * Проверяет валидность эндпойнта
     *
     * @param array $endpoint Конфигурация эндпойнта
     * @return bool
     */
    private function isValidEndpoint(array $endpoint): bool
    {
        if (!isset($endpoint['getData'], $endpoint['clear'])) {
            return false;
        }

        if (!is_string($endpoint['getData']) || !is_string($endpoint['clear'])) {
            return false;
        }

        // rowTitle опциональный
        if (isset($endpoint['rowTitle']) && !is_string($endpoint['rowTitle'])) {
            return false;
        }

        return true;
    }

    /**
     * Валидирует все собранные эндпойнты
     *
     * @param array $endpoints Массив эндпойнтов
     * @return array Отфильтрованный массив валидных эндпойнтов
     */
    public function validateEndpoints(array $endpoints): array
    {
        $validated = [];

        foreach ($endpoints as $moduleId => $moduleEndpoints) {
            if (!is_array($moduleEndpoints)) {
                continue;
            }

            $validatedModuleEndpoints = [];

            // Проверяем, один эндпойнт или набор
            if (isset($moduleEndpoints['getData']) && isset($moduleEndpoints['clear'])) {
                // Один эндпойнт
                if ($this->isValidEndpoint($moduleEndpoints)) {
                    $validatedModuleEndpoints = $moduleEndpoints;
                }
            } else {
                // Набор эндпойнтов
                foreach ($moduleEndpoints as $key => $endpoint) {
                    if ($this->isValidEndpoint($endpoint)) {
                        $validatedModuleEndpoints[$key] = $endpoint;
                    }
                }
            }

            if (!empty($validatedModuleEndpoints)) {
                $validated[$moduleId] = $validatedModuleEndpoints;
            }
        }

        return $validated;
    }
}
