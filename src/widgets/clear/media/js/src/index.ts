/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

import ClearWidget from './ClearWidget';

/**
 * Инициализация виджета при загрузке DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.querySelector('.clear-module-widget');
    if (widget instanceof HTMLElement) {
        new ClearWidget(widget);
    }
});

export default ClearWidget;
