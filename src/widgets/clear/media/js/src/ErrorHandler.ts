/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Класс для обработки ошибок
 */
export class CustomError extends Error {
    constructor(
        message: string,
        public details: string,
        public context?: unknown
    ) {
        super(message);
        this.name = 'CustomError';
    }
}

/**
 * Обработчик ошибок
 */
export class ErrorHandler {
    /**
     * Обрабатывает ошибку и отображает уведомление
     *
     * @param error Ошибка
     * @param context Контекст ошибки
     */
    handleError(error: unknown, context: string): void {
        let message = 'Произошла неизвестная ошибка';

        if (error instanceof CustomError) {
            message = `${error.message}${error.details ? ': ' + error.details : ''}`;
        } else if (error instanceof Error) {
            message = error.message;
        } else if (typeof error === 'string') {
            message = error;
        }

        console.error(`[${context}]`, error);

        if (typeof showAlert === 'function') {
            showAlert({
                message: message,
                type: 'error',
                duration: 5000,
            });
        } else {
            alert(message);
        }
    }
}
