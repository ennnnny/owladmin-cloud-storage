<?php


if (! function_exists('cloud_storage_trans')) {
    function cloud_storage_trans($key): array|string
    {
        return \Ennnnny\CloudStorage\CloudStorageServiceProvider::trans('cloud-storage.'.$key);
    }
}

if (! function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}

/**
 * 说明
 * 文件后缀判断，主要获取文件类型，根据类型存储不同目录
 * 判断依据根据env配置有关系
 * other属于其它类型
 *
 * @param  string  $fileName
 * @return string $format
 */
if (! function_exists('getAccept')) {
    function getAccept(string $fileName): string
    {
        $arr = explode('.', $fileName);
        $ext = end($arr);
        if (strpos(env('CLOUD_STORAGE_DOCUMENT_EXTENSION'), $ext) !== false) {
            $format = 'document';
        } elseif (strpos(env('CLOUD_STORAGE_VIDEO_EXTENSION'), $ext) !== false) {
            $format = 'video';
        } elseif (strpos(env('CLOUD_STORAGE_AUDIO_EXTENSION'), $ext) !== false) {
            $format = 'audio';
        } elseif (strpos(env('CLOUD_STORAGE_IMAGE_EXTENSION'), $ext) !== false) {
            $format = 'image';
        } else {
            $format = 'other';
        }

        return $format;
    }
}

/**
 * 获取源文件名
 *
 * @param  string  $fileName
 * @return string
 */
if (! function_exists('getFileName')) {
    function getFileName(string $fileName): string
    {
        $arr = explode('/', $fileName);

        return end($arr);
    }
}
