<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\contracts;

/**
 * Интерфейс для валидации эндпойнтов очистки
 */
interface ClearEndpointInterface
{
    /**
     * Валидирует конфигурацию эндпойнта
     *
     * @param array $endpoint Конфигурация эндпойнта
     * @return bool
     */
    public function validateEndpoint(array $endpoint): bool;

    /**
     * Валидирует данные, полученные от эндпойнта
     *
     * @param mixed $data Данные от эндпойнта
     * @return bool
     */
    public function validateData(mixed $data): bool;
}
