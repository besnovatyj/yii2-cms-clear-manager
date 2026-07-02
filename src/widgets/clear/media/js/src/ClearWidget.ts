/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import { ApiService } from './ApiService';
import { CustomError, ErrorHandler } from './ErrorHandler';

/** Данные одной ячейки: либо загруженное значение, либо ошибка загрузки. */
type CellData = { value: string } | { error: string };

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

        const allData: Record<string, Record<string, CellData>> = {};

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
    private async fetchModuleData(moduleEndpoints: ModuleEndpoints): Promise<Record<string, CellData>> {
        const data: Record<string, CellData> = {};

        if (this.isSingleEndpoint(moduleEndpoints)) {
            data['default'] = await this.loadCell(moduleEndpoints.getData);
        } else {
            for (const [key, endpoint] of Object.entries(moduleEndpoints)) {
                data[key] = await this.loadCell(endpoint.getData);
            }
        }

        return data;
    }

    /**
     * Загружает данные одной ячейки. Ошибка НЕ проглатывается в консоль молча — она возвращается
     * как {@link CellData} и затем видимо отображается в таблице (см. {@link createTableRow}).
     */
    private async loadCell(url: string): Promise<CellData> {
        try {
            const response = await this.apiService.post(url);
            return { value: response.data ?? 'N/A' };
        } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            console.error('Ошибка при получении данных:', error);
            return { error: message };
        }
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
    private render(allData: Record<string, Record<string, CellData>>): void {
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
    private renderModuleSection(moduleId: string, moduleData: Record<string, CellData>): HTMLElement {
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
                moduleData['default'],
                moduleEndpoints.clear,
                moduleEndpoints.rowTitle ?? 'данные'
            );
            tbody.appendChild(row);
        } else {
            // Несколько эндпойнтов
            for (const [key, endpoint] of Object.entries(moduleEndpoints)) {
                const row = this.createTableRow(
                    endpoint.rowTitle ?? key,
                    moduleData[key],
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
     * @param cell Данные ячейки (значение | ошибка | не загружено)
     * @param clearUrl URL для очистки
     * @param label Метка для кнопки
     * @returns HTML элемент строки
     */
    private createTableRow(title: string, cell: CellData | undefined, clearUrl: string, label: string): HTMLTableRowElement {
        const row = document.createElement('tr');

        let dataCell: string;
        if (cell === undefined) {
            dataCell = 'N/A';
        } else if ('error' in cell) {
            // Ошибку загрузки показываем видимо (а не прячем в консоль); полный текст — в подсказке.
            dataCell = `<span class="text-danger fw-bold" title="${this.escapeHtml(cell.error)}">⚠ Ошибка загрузки</span>`;
        } else {
            dataCell = this.escapeHtml(cell.value);
        }

        row.innerHTML = `
            <td>${this.escapeHtml(title)}</td>
            <td>${dataCell}</td>
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

    /** Экранирует строку для безопасной вставки в HTML (значения/ошибки от сервера). */
    private escapeHtml(value: string): string {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
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
            // Ошибка прилетит исключением из ApiService.post (сервер отдаёт 4xx/5xx) — её ловит catch.
            if (typeof showAlert === 'function') {
                showAlert({
                    message: response.message || `${label}: успешно очищено`,
                    type: 'success',
                    duration: 3000,
                });
            }
            await this.fetchAndRender();
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
