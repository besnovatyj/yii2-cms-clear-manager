/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import { ApiService } from './ApiService';
import { CustomError, ErrorHandler } from './ErrorHandler';

/**
 * Главный класс виджета очистки
 */
class ClearWidget {
    private element: HTMLElement;
    private readonly endpoints: AllEndpoints;
    private apiService: ApiService;
    private errorHandler: ErrorHandler;

    constructor(element: HTMLElement) {
        if (!element.dataset.endpoints) {
            throw new CustomError('constructor', 'Endpoints dataset is missing', {});
        }

        this.element = element;
        this.endpoints = JSON.parse(element.dataset.endpoints) as AllEndpoints;
        this.apiService = new ApiService();
        this.errorHandler = new ErrorHandler();
        this.initialize();
    }

    /**
     * Инициализирует виджет
     */
    private async initialize(): Promise<void> {
        await this.fetchAndRender();
    }

    /**
     * Получает данные со всех эндпойнтов и рендерит UI
     */
    private async fetchAndRender(): Promise<void> {
        this.element.innerHTML = '<div class="loading-indicator"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Загрузка...</span></div></div>';

        const allData: Record<string, Record<string, string>> = {};

        for (const [moduleId, moduleEndpoints] of Object.entries(this.endpoints)) {
            allData[moduleId] = await this.fetchModuleData(moduleEndpoints);
        }

        this.render(allData);
    }

    /**
     * Получает данные для одного модуля
     *
     * @param moduleEndpoints Эндпойнты модуля
     * @returns Объект с данными
     */
    private async fetchModuleData(moduleEndpoints: ModuleEndpoints): Promise<Record<string, string>> {
        const data: Record<string, string> = {};

        if (this.isSingleEndpoint(moduleEndpoints)) {
            // Один эндпойнт
            try {
                const response = await this.apiService.post(moduleEndpoints.getData);
                if (response.status === 'success' && response.data) {
                    data['default'] = response.data;
                }
            } catch (error) {
                console.error('Ошибка при получении данных:', error);
            }
        } else {
            // Несколько эндпойнтов
            for (const [key, endpoint] of Object.entries(moduleEndpoints)) {
                try {
                    const response = await this.apiService.post(endpoint.getData);
                    if (response.status === 'success' && response.data) {
                        data[key] = response.data;
                    }
                } catch (error) {
                    console.error(`Ошибка при получении данных для ${key}:`, error);
                }
            }
        }

        return data;
    }

    /**
     * Проверяет, является ли эндпойнт одиночным
     */
    private isSingleEndpoint(endpoints: ModuleEndpoints): endpoints is EndpointConfig {
        return 'getData' in endpoints && 'clear' in endpoints;
    }

    /**
     * Рендерит UI виджета
     *
     * @param allData Данные со всех модулей
     */
    private render(allData: Record<string, Record<string, string>>): void {
        this.element.innerHTML = '';

        // Кнопка полной очистки и обновления
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'button-container d-flex gap-2 flex-wrap mb-3';

        const refreshButton = document.createElement('button');
        refreshButton.textContent = 'Обновить данные';
        refreshButton.className = 'btn btn-primary';
        refreshButton.addEventListener('click', async () => {
            await this.fetchAndRender();
        });
        buttonContainer.appendChild(refreshButton);

        const clearAllButton = document.createElement('button');
        clearAllButton.textContent = 'Очистить все';
        clearAllButton.className = 'btn btn-danger ms-auto';
        clearAllButton.addEventListener('click', async () => {
            if (confirm('Вы уверены, что хотите очистить все данные?')) {
                await this.clearAll();
            }
        });
        buttonContainer.appendChild(clearAllButton);

        this.element.appendChild(buttonContainer);

        // Рендерим данные по модулям
        for (const [moduleId, moduleData] of Object.entries(allData)) {
            const moduleSection = this.renderModuleSection(moduleId, moduleData);
            this.element.appendChild(moduleSection);
        }
    }

    /**
     * Рендерит секцию модуля
     *
     * @param moduleId ID модуля
     * @param moduleData Данные модуля
     * @returns HTML элемент
     */
    private renderModuleSection(moduleId: string, moduleData: Record<string, string>): HTMLElement {
        const section = document.createElement('div');
        section.className = 'module-section';

        const title = document.createElement('h3');
        title.textContent = `Модуль: ${moduleId}`;
        section.appendChild(title);

        const table = document.createElement('table');
        table.className = 'table table-bordered table-hover';

        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th scope="col">Элемент</th>
                <th scope="col">Данные</th>
                <th scope="col">Действие</th>
            </tr>
        `;
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        const moduleEndpoints = this.endpoints[moduleId];

        if (this.isSingleEndpoint(moduleEndpoints)) {
            // Один эндпойнт
            const row = this.createTableRow(
                moduleEndpoints.rowTitle ?? 'Данные',
                moduleData['default'] ?? 'N/A',
                moduleEndpoints.clear,
                moduleEndpoints.rowTitle ?? 'данные'
            );
            tbody.appendChild(row);
        } else {
            // Несколько эндпойнтов
            for (const [key, endpoint] of Object.entries(moduleEndpoints)) {
                const row = this.createTableRow(
                    endpoint.rowTitle ?? key,
                    moduleData[key] ?? 'N/A',
                    endpoint.clear,
                    endpoint.rowTitle ?? key
                );
                tbody.appendChild(row);
            }
        }

        table.appendChild(tbody);
        section.appendChild(table);

        return section;
    }

    /**
     * Создает строку таблицы
     *
     * @param title Название
     * @param data Данные
     * @param clearUrl URL для очистки
     * @param label Метка для кнопки
     * @returns HTML элемент строки
     */
    private createTableRow(title: string, data: string, clearUrl: string, label: string): HTMLTableRowElement {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${title}</td>
            <td>${data}</td>
            <td>
                <button class="btn btn-sm btn-danger clear-item" data-url="${clearUrl}" data-label="${label}">
                    Очистить
                </button>
            </td>
        `;

        const button = row.querySelector('.clear-item') as HTMLButtonElement;
        button.addEventListener('click', async () => {
            await this.handleClear(clearUrl, label);
        });

        return row;
    }

    /**
     * Обрабатывает очистку по одному эндпойнту
     *
     * @param url URL эндпойнта
     * @param label Метка
     */
    private async handleClear(url: string, label: string): Promise<void> {
        try {
            const response = await this.apiService.post(url);
            if (response.status === 'success') {
                if (typeof showAlert === 'function') {
                    showAlert({
                        message: response.message || `${label}: успешно очищено`,
                        type: 'success',
                        duration: 3000,
                    });
                }
                await this.fetchAndRender();
            } else {
                throw new CustomError(
                    String(response.message),
                    String(response.data?.message ?? ''),
                    response
                );
            }
        } catch (error) {
            this.errorHandler.handleError(error, 'handleClear');
        }
    }

    /**
     * Очищает все данные по всем модулям
     */
    private async clearAll(): Promise<void> {
        const promises: Promise<void>[] = [];

        for (const moduleEndpoints of Object.values(this.endpoints)) {
            if (this.isSingleEndpoint(moduleEndpoints)) {
                promises.push(this.apiService.post(moduleEndpoints.clear).then(() => {}));
            } else {
                for (const endpoint of Object.values(moduleEndpoints)) {
                    promises.push(this.apiService.post(endpoint.clear).then(() => {}));
                }
            }
        }

        try {
            await Promise.all(promises);
            if (typeof showAlert === 'function') {
                showAlert({
                    message: 'Все данные успешно очищены',
                    type: 'success',
                    duration: 3000,
                });
            }
            await this.fetchAndRender();
        } catch (error) {
            this.errorHandler.handleError(error, 'clearAll');
        }
    }
}

export default ClearWidget;
