<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\ClearManager\services;

use Besnovatyj\Helpers\FilesystemHelper;
use Exception;
use Yii;
use yii\base\ErrorException;
use yii\helpers\FileHelper;

/**
 * Сервис для работы с очисткой директорий модуля Clear
 */
class DirectoryClearService
{
    /**
     * @var array Пути к директориям из конфигурации
     */
    private array $directories;

    public function __construct()
    {
        $module = Yii::$app->getModule('ClearManager');
        $this->directories = $module->params['clear-dirs'] ?? [];
    }

    /**
     * Возвращает путь к директории, только если он задан в конфиге И реально
     * существует на диске. Иначе — null.
     *
     * Часть директорий (например, runtime/debug, runtime/mail) в проде могут
     * отсутствовать: они создаются лениво только при соответствующей активности.
     * Раньше guard проверял лишь `!== null`, из-за чего FilesystemHelper кидал
     * InvalidArgumentException на несуществующем пути. Единая точка резолва
     * приводит отсутствующий каталог к штатному «нечего показывать/очищать».
     *
     * @param string $key Ключ директории в конфиге clear-dirs
     * @return string|null
     */
    private function resolveExistingDir(string $key): ?string
    {
        $path = $this->directories[$key] ?? null;

        return ($path !== null && is_dir($path)) ? $path : null;
    }

    /**
     * Очищает кеш приложения
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        return Yii::$app->cache->flush();
    }

    /**
     * Очищает ресурсы фронтэнда
     *
     * @return bool
     * @throws Exception
     */
    public function clearFrontAssets(): bool
    {
        if (Yii::$app->assetManager->linkAssets) {
            return true;
        }

        $path = $this->resolveExistingDir('frontAssets');
        if ($path === null) {
            return false;
        }

        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Очищает ресурсы бэкэнда
     *
     * @return bool
     * @throws Exception
     */
    public function clearBackAssets(): bool
    {
        if (Yii::$app->assetManager->linkAssets) {
            return true;
        }

        $path = $this->resolveExistingDir('backAssets');
        if ($path === null) {
            return false;
        }

        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Очищает логи
     *
     * @return bool
     * @throws Exception
     */
    public function clearLogs(): bool
    {
        $path = $this->resolveExistingDir('logs');
        if ($path === null) {
            return false;
        }

        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Очищает debug панель
     *
     * @return bool
     * @throws Exception
     */
    public function clearDebug(): bool
    {
        $path = $this->resolveExistingDir('debug');
        if ($path === null) {
            return false;
        }
        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Очищает отладочные письма
     *
     * @return bool
     * @throws Exception
     */
    public function clearMail(): bool
    {
        $path = $this->resolveExistingDir('mail');
        if ($path === null) {
            return false;
        }

        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Очищает кеш статики
     *
     * @return bool
     * @throws Exception
     */
    public function clearStatic(): bool
    {
        $path = $this->resolveExistingDir('static');
        if ($path === null) {
            return false;
        }

        return FilesystemHelper::deleteDirContents($path, false, ['.gitignore']);
    }

    /**
     * Получает размер кеша
     *
     * @return string Отформатированная строка с размером
     */
    public function getCacheData(): string
    {
//        return 'N/A'; // TODO - реализовать проверку самому? // https://www.php.net/manual/ru/book.apcu.php
        $size =  (new \APCUIterator)->getTotalSize();
        return $this->formatBytes($size);
    }

    /**
     * Получает данные о ресурсах фронтэнда
     *
     * @return string Отформатированная строка с размером
     * @throws Exception
     */
    public function getFrontAssetsData(): string
    {
        if (Yii::$app->assetManager->linkAssets) {
            return 'Ссылки (не требуется)';
        }

        $path = $this->resolveExistingDir('frontAssets');
        if ($path === null) {
            return 'N/A';
        }

        $size = FilesystemHelper::getDirSize($path, true);
        return $this->formatBytes($size);
    }

    /**
     * Получает данные о ресурсах бэкэнда
     *
     * @return string Отформатированная строка с размером
     * @throws Exception
     */
    public function getBackAssetsData(): string
    {
        if (Yii::$app->assetManager->linkAssets) {
            return 'Ссылки (не требуется)';
        }

        $path = $this->resolveExistingDir('backAssets');
        if ($path === null) {
            return 'N/A';
        }

        $size = FilesystemHelper::getDirSize($path, true);
        return $this->formatBytes($size);
    }

    /**
     * Получает данные о логах
     *
     * @return string Отформатированная строка с размером
     * @throws Exception
     */
    public function getLogsData(): string
    {
        $path = $this->resolveExistingDir('logs');
        if ($path === null) {
            return 'N/A';
        }

        $size = FilesystemHelper::getDirSize($path, true);
        return $this->formatBytes($size);
    }

    /**
     * Получает данные о debug панели
     *
     * @return string Отформатированная строка с количеством
     * @throws Exception
     */
    public function getDebugData(): string
    {
        $path = $this->resolveExistingDir('debug');
        if ($path === null) {
            return 'N/A';
        }

        $count = FilesystemHelper::countFiles($path);
        return "{$count} файлов";
    }

    /**
     * Получает данные об отладочных письмах
     *
     * @return string Отформатированная строка с количеством
     * @throws Exception
     */
    public function getMailData(): string
    {
        $path = $this->resolveExistingDir('mail');
        if ($path === null) {
            return 'N/A';
        }

        $count = FilesystemHelper::countFiles($path);
        return "{$count} писем";
    }

    /**
     * Получает данные о кеше статики
     *
     * @return string Отформатированная строка с размером
     * @throws Exception
     */
    public function getStaticData(): string
    {
        $path = $this->resolveExistingDir('static');
        if ($path === null) {
            return 'N/A';
        }

        $size = FilesystemHelper::getDirSize($path, true);
        return $this->formatBytes($size);
    }

    /**
     * Форматирует размер в байтах в читаемый формат
     *
     * @param int $bytes Размер в байтах
     * @return string
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
