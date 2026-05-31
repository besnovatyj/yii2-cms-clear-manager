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
            $moduleInstance = $this->getModuleInstance($moduleId, $module);
            if ($moduleInstance === null) {
                continue;
            }

            $moduleEndpoints = $this->extractClearEndpoints($moduleInstance);
            if (!empty($moduleEndpoints)) {
                $endpoints[$moduleId] = $moduleEndpoints;
            }
        }

        return $endpoints;
    }

    /**
     * Получает экземпляр модуля
     *
     * @param string $moduleId ID модуля
     * @param mixed $module Конфигурация или экземпляр модуля
     * @return Module|null
     */
    private function getModuleInstance(string $moduleId, mixed $module): ?Module
    {
        try {
            if ($module instanceof Module) {
                return $module;
            }

            return Yii::$app->getModule($moduleId);
        } catch (\Exception $e) {
            Yii::warning("Не удалось получить модуль {$moduleId}: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Извлекает эндпойнты очистки из конфигурации модуля
     *
     * @param Module $module Экземпляр модуля
     * @return array
     */
    private function extractClearEndpoints(Module $module): array
    {
        $params = $module->params ?? [];
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
