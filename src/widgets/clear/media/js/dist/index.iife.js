this.ClearWidget = function() {
  "use strict";
  class ApiService {
    /**
     * Получает CSRF токен из meta-тега
     *
     * @returns CSRF токен
     * @throws Error если токен не найден
     */
    getCsrfToken() {
      const tokenElement = document.head.querySelector('[name="csrf-token"]');
      if (!tokenElement) {
        throw new Error("CSRF token not found");
      }
      return tokenElement.getAttribute("content");
    }
    /**
     * Возвращает заголовки для запроса
     *
     * @returns Объект с заголовками
     */
    getRequestHeaders() {
      return {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-Token": this.getCsrfToken(),
        "Content-Type": "application/json"
      };
    }
    /**
     * Выполняет POST запрос к API
     *
     * @param url URL эндпойнта
     * @param body Тело запроса (опционально)
     * @returns Promise с ответом API
     */
    async post(url, body) {
      const response = await fetch(url, {
        method: "POST",
        headers: this.getRequestHeaders(),
        body: body ? JSON.stringify(body) : void 0
      });
      const payload = await response.json().catch(() => null);
      if (!response.ok) {
        const message = payload && (payload.message || payload.name) || `HTTP ${response.status}`;
        throw new Error(message);
      }
      return payload;
    }
  }
  class CustomError extends Error {
    constructor(message, details, context) {
      super(message);
      this.details = details;
      this.context = context;
      this.name = "CustomError";
    }
  }
  class ErrorHandler {
    /**
     * Обрабатывает ошибку и отображает уведомление
     *
     * @param error Ошибка
     * @param context Контекст ошибки
     */
    handleError(error, context) {
      let message = "Произошла неизвестная ошибка";
      if (error instanceof CustomError) {
        message = `${error.message}${error.details ? ": " + error.details : ""}`;
      } else if (error instanceof Error) {
        message = error.message;
      } else if (typeof error === "string") {
        message = error;
      }
      console.error(`[${context}]`, error);
      if (typeof showAlert === "function") {
        showAlert({
          message,
          type: "error",
          duration: 5e3
        });
      } else {
        alert(message);
      }
    }
  }
  class ClearWidget {
    constructor(element) {
      if (!element.dataset.endpoints) {
        throw new CustomError("constructor", "Endpoints dataset is missing", {});
      }
      this.element = element;
      this.endpoints = JSON.parse(element.dataset.endpoints);
      this.apiService = new ApiService();
      this.errorHandler = new ErrorHandler();
      this.initialize();
    }
    /**
     * Инициализирует виджет
     */
    async initialize() {
      await this.fetchAndRender();
    }
    /**
     * Получает данные со всех эндпойнтов и рендерит UI
     */
    async fetchAndRender() {
      this.element.innerHTML = '<div class="loading-indicator"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Загрузка...</span></div></div>';
      const allData = {};
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
    async fetchModuleData(moduleEndpoints) {
      const data = {};
      if (this.isSingleEndpoint(moduleEndpoints)) {
        data["default"] = await this.loadCell(moduleEndpoints.getData);
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
    async loadCell(url) {
      try {
        const response = await this.apiService.post(url);
        return { value: response.data ?? "N/A" };
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        console.error("Ошибка при получении данных:", error);
        return { error: message };
      }
    }
    /**
     * Проверяет, является ли эндпойнт одиночным
     */
    isSingleEndpoint(endpoints) {
      return "getData" in endpoints && "clear" in endpoints;
    }
    /**
     * Рендерит UI виджета
     *
     * @param allData Данные со всех модулей
     */
    render(allData) {
      this.element.innerHTML = "";
      const buttonContainer = document.createElement("div");
      buttonContainer.className = "button-container d-flex gap-2 flex-wrap mb-3";
      const refreshButton = document.createElement("button");
      refreshButton.textContent = "Обновить данные";
      refreshButton.className = "btn btn-primary";
      refreshButton.addEventListener("click", async () => {
        await this.fetchAndRender();
      });
      buttonContainer.appendChild(refreshButton);
      const clearAllButton = document.createElement("button");
      clearAllButton.textContent = "Очистить все";
      clearAllButton.className = "btn btn-danger ms-auto";
      clearAllButton.addEventListener("click", async () => {
        if (confirm("Вы уверены, что хотите очистить все данные?")) {
          await this.clearAll();
        }
      });
      buttonContainer.appendChild(clearAllButton);
      this.element.appendChild(buttonContainer);
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
    renderModuleSection(moduleId, moduleData) {
      const section = document.createElement("div");
      section.className = "module-section";
      const title = document.createElement("h3");
      title.textContent = `Модуль: ${moduleId}`;
      section.appendChild(title);
      const table = document.createElement("table");
      table.className = "table table-bordered table-hover";
      const thead = document.createElement("thead");
      thead.innerHTML = `
            <tr>
                <th scope="col">Элемент</th>
                <th scope="col">Данные</th>
                <th scope="col">Действие</th>
            </tr>
        `;
      table.appendChild(thead);
      const tbody = document.createElement("tbody");
      const moduleEndpoints = this.endpoints[moduleId];
      if (this.isSingleEndpoint(moduleEndpoints)) {
        const row = this.createTableRow(
          moduleEndpoints.rowTitle ?? "Данные",
          moduleData["default"],
          moduleEndpoints.clear,
          moduleEndpoints.rowTitle ?? "данные"
        );
        tbody.appendChild(row);
      } else {
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
    createTableRow(title, cell, clearUrl, label) {
      const row = document.createElement("tr");
      let dataCell;
      if (cell === void 0) {
        dataCell = "N/A";
      } else if ("error" in cell) {
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
      const button = row.querySelector(".clear-item");
      button.addEventListener("click", async () => {
        await this.handleClear(clearUrl, label);
      });
      return row;
    }
    /** Экранирует строку для безопасной вставки в HTML (значения/ошибки от сервера). */
    escapeHtml(value) {
      const div = document.createElement("div");
      div.textContent = value;
      return div.innerHTML;
    }
    /**
     * Обрабатывает очистку по одному эндпойнту
     *
     * @param url URL эндпойнта
     * @param label Метка
     */
    async handleClear(url, label) {
      try {
        const response = await this.apiService.post(url);
        if (typeof showAlert === "function") {
          showAlert({
            message: response.message || `${label}: успешно очищено`,
            type: "success",
            duration: 3e3
          });
        }
        await this.fetchAndRender();
      } catch (error) {
        this.errorHandler.handleError(error, "handleClear");
      }
    }
    /**
     * Очищает все данные по всем модулям
     */
    async clearAll() {
      const promises = [];
      for (const moduleEndpoints of Object.values(this.endpoints)) {
        if (this.isSingleEndpoint(moduleEndpoints)) {
          promises.push(this.apiService.post(moduleEndpoints.clear).then(() => {
          }));
        } else {
          for (const endpoint of Object.values(moduleEndpoints)) {
            promises.push(this.apiService.post(endpoint.clear).then(() => {
            }));
          }
        }
      }
      try {
        await Promise.all(promises);
        if (typeof showAlert === "function") {
          showAlert({
            message: "Все данные успешно очищены",
            type: "success",
            duration: 3e3
          });
        }
        await this.fetchAndRender();
      } catch (error) {
        this.errorHandler.handleError(error, "clearAll");
      }
    }
  }
  document.addEventListener("DOMContentLoaded", () => {
    const widget = document.querySelector(".clear-module-widget");
    if (widget instanceof HTMLElement) {
      new ClearWidget(widget);
    }
  });
  return ClearWidget;
}();
//# sourceMappingURL=index.iife.js.map
