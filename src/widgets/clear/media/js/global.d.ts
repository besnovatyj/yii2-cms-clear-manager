/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Глобальные типы для виджета очистки
 */

/**
 * Конфигурация одного эндпойнта
 */
interface EndpointConfig {
    rowTitle?: string;
    getData: string;
    clear: string;
}

/**
 * Структура эндпойнтов модуля.
 * Может быть либо одним эндпойнтом, либо объектом с несколькими эндпойнтами.
 */
type ModuleEndpoints = EndpointConfig | Record<string, EndpointConfig>;

/**
 * Полная структура эндпойнтов всех модулей
 */
interface AllEndpoints {
    [moduleId: string]: ModuleEndpoints;
}

/**
 * Ответ API при успехе
 */
interface SuccessResponse {
    status: 'success';
    message?: string;
    data?: string;
}

/**
 * Ошибки больше НЕ приходят конвертом приложения: сервер отдаёт их с реальным HTTP-статусом
 * (4xx/5xx) в нативном формате Yii ErrorHandler. `ApiService.post` парсит их и бросает `Error`
 * с серверным `message`, поэтому в коде виджета успешный ответ — это всегда `SuccessResponse`.
 */

/**
 * Глобальная функция для отображения уведомлений
 */
declare function showAlert(options: {
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
    duration?: number;
}): void;
