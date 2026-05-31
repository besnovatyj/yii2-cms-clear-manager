# Модуль Clear

Модуль для управления и очистки временных данных приложения Yii2-cms.

## Описание

Модуль `ClearManager` предоставляет централизованный функционал для:
- Сбора информации о временных данных из различных модулей приложения
- Отображения этой информации в удобном интерфейсе
- Очистки временных данных через унифицированный интерфейс

## Принцип работы

1. Модуль `ClearManager` при инициализации виджета собирает эндпойнты из конфигураций всех модулей приложения через `Yii::$app->getModules()`
2. Если у модуля в конфигурации есть секция `params['endpoints']['clear']`, то эти эндпойнты добавляются в общий список
3. Виджет валидирует все собранные эндпойнты и проверяет права доступа пользователя
4. Виджет отправляет запросы к эндпойнтам получения данных и отображает результаты
5. Пользователь может очистить данные для каждого модуля отдельно или все сразу

## Контракты и соглашения

### Формат эндпойнтов в конфигурации модуля

Каждый модуль может определить эндпойнты для получения данных и их очистки в своей конфигурации:

```php
// Besnovatyj/ClearManager/{moduleName}/config/config.php
<?php
return [
    'id' => 'moduleName',
    'params' => [
        'endpoints' => [
            'clear' => [
                // Вариант 1: Один набор эндпойнтов для модуля
                'getData' => '/module-name/backend/controller/get-data',
                'clear' => '/module-name/backend/controller/clear-data',
                'rowTitle' => 'Название данных', // опционально

                // Вариант 2: Несколько наборов эндпойнтов
                'cache' => [
                    'rowTitle' => 'Кеш модуля',
                    'getData' => '/module-name/backend/cache/get-data',
                    'clear' => '/module-name/backend/cache/clear',
                ],
                'logs' => [
                    'rowTitle' => 'Логи модуля',
                    'getData' => '/module-name/backend/logs/get-data',
                    'clear' => '/module-name/backend/logs/clear',
                ],
            ],
        ],
    ],
];
```

### Требования к эндпойнтам

#### Эндпойнт получения данных (`getData`)

**Требования:**
- Метод: `POST`
- Заголовок: `X-Requested-With-Fetch: true`
- Формат ответа: JSON

**Формат успешного ответа:**
```json
{
    "status": "success",
    "data": "123.45 МБ"
}
```

Поле `data` должно содержать **отформатированную строку** для отображения в таблице виджета. Модуль сам отвечает за:
- Подсчет размера/количества данных
- Форматирование с единицами измерения (МБ, КБ, штук, файлов и т.д.)
- Локализацию текста

**Примеры валидных значений для `data`:**
- `"15.23 МБ"`
- `"42 файла"`
- `"1,234 записей"`
- `"N/A"` (если данные недоступны)
- `"Ссылки (не требуется)"` (если очистка не нужна)

**Формат ответа при ошибке:**
```json
{
    "status": "error",
    "message": "Описание ошибки"
}
```

#### Эндпойнт очистки данных (`clear`)

**Требования:**
- Метод: `POST`
- Заголовок: `X-Requested-With-Fetch: true`
- Формат ответа: JSON

**Формат успешного ответа:**
```json
{
    "status": "success",
    "message": "Данные успешно очищены"
}
```

**Формат ответа при ошибке:**
```json
{
    "status": "error",
    "message": "Описание ошибки"
}
```

## Примеры реализации

### Пример 1: Простой модуль с одним набором данных

```php
// Besnovatyj/ClearManager/blog/config/config.php
<?php
return [
    'id' => 'blog',
    'params' => [
        'endpoints' => [
            'clear' => [
                'rowTitle' => 'Кеш постов блога',
                'getData' => '/blog/backend/cache/get-data',
                'clear' => '/blog/backend/cache/clear-data',
            ],
        ],
    ],
];
```

```php
// Besnovatyj/ClearManager/blog/controllers/backend/CacheController.php
<?php

declare(strict_types=1);

namespace modules\blog\controllers\backend;

use modules\blog\services\BlogCacheService;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;

class CacheController extends \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;
    
    private BlogCacheService $service;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['POST'],
                ],
            ],
        ];
    }

    public function __construct($id, $module, BlogCacheService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    /**
     * Получить данные о кеше
     */
    public function actionGetData(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->isFetchRequest()) {
            return [
                'status' => 'error',
                'message' => 'Отсутствует заголовок X-Requested-With-Fetch'
            ];
        }

        try {
            // Получаем размер кеша в байтах
            $sizeInBytes = $this->service->getCacheSize();

            // Форматируем в читаемый вид
            $formattedSize = $this->formatBytes($sizeInBytes);

            return [
                'status' => 'success',
                'data' => $formattedSize, // Например: "15.23 МБ"
            ];
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении данных о кеше'
            ];
        }
    }

    /**
     * Очистить кеш
     */
    public function actionClearData(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->isFetchRequest()) {
            return [
                'status' => 'error',
                'message' => 'Отсутствует заголовок X-Requested-With-Fetch'
            ];
        }

        try {
            $this->service->clearCache();

            return [
                'status' => 'success',
                'message' => 'Кеш постов успешно очищен',
            ];
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'message' => 'Ошибка при очистке кеша'
            ];
        }
    }

    /**
     * Форматирует байты в читаемый формат
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        $size = (float)$bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return sprintf('%.2f %s', $size, $units[$unitIndex]);
    }
}
```

### Пример 2: Модуль с несколькими наборами данных

```php
// Besnovatyj/ClearManager/gallery/config/config.php
<?php
return [
    'id' => 'gallery',
    'params' => [
        'endpoints' => [
            'clear' => [
                'thumbnails' => [
                    'rowTitle' => 'Миниатюры изображений',
                    'getData' => '/gallery/backend/thumbnails/get-data',
                    'clear' => '/gallery/backend/thumbnails/clear',
                ],
                'originalCache' => [
                    'rowTitle' => 'Кеш оригиналов',
                    'getData' => '/gallery/backend/original/get-data',
                    'clear' => '/gallery/backend/original/clear',
                ],
                'metadata' => [
                    'rowTitle' => 'Метаданные изображений',
                    'getData' => '/gallery/backend/metadata/get-data',
                    'clear' => '/gallery/backend/metadata/clear',
                ],
            ],
        ],
    ],
];
```

### Пример 3: Модуль с подсчетом файлов

```php
// Besnovatyj/ClearManager/documents/controllers/backend/CacheController.php
public function actionGetData(): array
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!$this->isFetchRequest()) {
        return ['status' => 'error', 'message' => 'Invalid request'];
    }

    try {
        $count = $this->service->getDocumentsCount();

        // Правильное склонение числительных
        $word = $this->plural($count, ['документ', 'документа', 'документов']);

        return [
            'status' => 'success',
            'data' => "{$count} {$word}", // Например: "42 документа"
        ];
    } catch (\Exception $e) {
        return ['status' => 'error', 'message' => YII_DEBUG ? VarDumper::dumpAsString($e->getMessage()) : 'Ошибка';];
    }
}

/**
 * Склонение числительных
 */
private function plural(int $n, array $forms): string
{
    return $forms[
        ($n % 10 == 1 && $n % 100 != 11)
            ? 0
            : (($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20))
                ? 1
                : 2)
    ];
}
```

## Структура модуля Clear

```
/Besnovatyj/ClearManager/ClearManager/
├── composer.json                      # Composer пакет
├── Module.php                         # Главный класс модуля
├── readme.md                          # Документация
├── config/
│   ├── adminMenu.php                  # Пункты меню админки
│   ├── config.php                     # Основная конфигурация
│   ├── container.php                  # DI контейнер
│   ├── dependencies.php               # Зависимости
│   ├── options.php                    # Настраиваемые опции
│   └── schema.php                     # Схема БД (пустая)
├── contracts/
│   ├── ClearEndpointInterface.php     # Интерфейс для валидации эндпойнтов
│   └── ClearDataInterface.php         # Интерфейс для данных
├── controllers/backend/
│   ├── IndexController.php            # Главная страница с виджетом
│   └── DataController.php             # Эндпойнты для собственных данных модуля
├── services/
│   ├── EndpointCollectorService.php   # Сбор эндпойнтов из модулей
│   └── DirectoryClearService.php      # Работа с директориями
├── views/backend/
│   └── index/
│       └── index.php                  # Страница с виджетом
└── widgets/clear/
    ├── ClearWidget.php                # PHP класс виджета
    ├── Assets.php                     # Asset bundle
    ├── views/
    │   └── index.php                  # View виджета
    └── media/
        ├── css/
        │   └── clear.css              # Стили
        └── js/
            ├── package.json           # NPM зависимости
            ├── tsconfig.json          # TypeScript конфигурация
            ├── vite.config.ts         # Vite конфигурация
            ├── global.d.ts            # Глобальные типы
            ├── src/
            │   ├── index.ts           # Точка входа
            │   ├── ClearWidget.ts     # Главный класс виджета
            │   ├── ApiService.ts      # Работа с API
            │   └── ErrorHandler.ts    # Обработка ошибок
            └── dist/
                ├── index.js           # Скомпилированный JS
                └── index.js.map       # Source map
```

## Сборка TypeScript

Перед использованием виджета необходимо собрать TypeScript файлы:

```bash
cd /Besnovatyj/ClearManager/widgets/clear/media/js
npm install
npm run build
```

Для разработки с автоматической пересборкой:

```bash
npm run watch
```

## Использование виджета

```php
// В любом view-файле бэкэнда
use Besnovatyj\ClearManager\widgets\clear\ClearWidget;

echo ClearWidget::widget();
```

Или на отдельной странице (уже реализовано в `/ClearManager/backend/index/index`):

```php
// /Besnovatyj/ClearManager/views/backend/index/index.php
<?php
use Besnovatyj\ClearManager\widgets\clear\ClearWidget;

$this->title = 'Управление временными данными';
?>

<div class="clear-index">
    <h1><?= $this->title ?></h1>
    <?= ClearWidget::widget() ?>
</div>
```

## Собственные эндпойнты модуля Clear

Модуль `ClearManager` сам предоставляет эндпойнты для очистки общих директорий приложения (определенных в `config/config.php`):

- Кеш приложения
- Ресурсы фронтэнда и бэкэнда
- Логи
- Debug панель
- Отладочные письма
- Кеш статики

Эти эндпойнты автоматически включаются в виджет.

## Особенности реализации

### Проверка прав доступа

Виджет автоматически фильтрует эндпойнты по правам доступа пользователя через `Helper::checkRoute()`.

### Валидация эндпойнтов

`EndpointCollectorService` валидирует все собранные эндпойнты на соответствие контракту:
- Наличие обязательных полей `getData` и `clear`
- Правильность типов данных
- Корректность формата

### Обработка ошибок

- PHP: Все ошибки логируются через `Yii::error()`
- TypeScript: `ErrorHandler` отображает ошибки через глобальную функцию `showAlert()` или стандартный `alert()`

### Безопасность

- Все запросы требуют заголовок `X-Requested-With-Fetch: true`
- Все действия контроллеров ограничены методом `POST`
- Проверка прав доступа на уровне виджета
- При очистке директорий файлы `.gitignore` не удаляются
- При очистке ассетов проверяется `Yii::$app->assetManager->linkAssets`

## Расширение функциональности

Для добавления поддержки очистки в ваш модуль:

1. Добавьте конфигурацию эндпойнтов в `config/config.php`
2. Реализуйте контроллер с действиями для получения данных и очистки
3. Убедитесь, что действия возвращают данные в правильном формате
4. Виджет автоматически подхватит новые эндпойнты

Не требуется:
- Регистрация модуля где-либо еще
- Изменение кода модуля `ClearManager`
- Реализация специальных интерфейсов (хотя можно для консистентности)

## Лицензия

Proprietary
