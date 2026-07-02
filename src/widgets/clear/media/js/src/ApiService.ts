/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Сервис для работы с API запросами
 */
export class ApiService {
    /**
     * Получает CSRF токен из meta-тега
     *
     * @returns CSRF токен
     * @throws Error если токен не найден
     */
    private getCsrfToken(): string {
        const tokenElement = document.head.querySelector('[name="csrf-token"]');
        if (!tokenElement) {
            throw new Error('CSRF token not found');
        }
        return tokenElement.getAttribute('content')!;
    }

    /**
     * Возвращает заголовки для запроса
     *
     * @returns Объект с заголовками
     */
    private getRequestHeaders(): HeadersInit {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': this.getCsrfToken(),
            'Content-Type': 'application/json',
        };
    }

    /**
     * Выполняет POST запрос к API
     *
     * @param url URL эндпойнта
     * @param body Тело запроса (опционально)
     * @returns Promise с ответом API
     */
    async post(url: string, body?: unknown): Promise<SuccessResponse> {
        const response = await fetch(url, {
            method: 'POST',
            headers: this.getRequestHeaders(),
            body: body ? JSON.stringify(body) : undefined,
        });

        // Тело читаем всегда: при ошибке в нём лежит нативный конверт Yii ErrorHandler
        // ({ name, message, code, status, ... }).
        const payload = await response.json().catch(() => null);

        if (!response.ok) {
            const message =
                (payload && (payload.message || payload.name)) || `HTTP ${response.status}`;
            throw new Error(message);
        }

        return payload as SuccessResponse;
    }
}
