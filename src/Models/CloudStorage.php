<?php

namespace Ennnnny\CloudStorage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class CloudStorage extends Base
{
    protected $table = 'admin_cloud_storage';

    public function config(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value, true) : [],
            set: fn ($value) => $value ? json_encode($value) : json_encode([])
        );
    }

    /**
     * 钩子
     */
    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (admin_user()) {
                $model->created_user = admin_user()->id;
            } else {
                $model->created_user = 0;
            }
            $model->setCache($model);
        });
        static::updating(function ($model) {
            if (admin_user()) {
                $model->updated_user = admin_user()->id;
            } else {
                $model->updated_user = 0;
            }
            $model->setCache($model);
        });
        static::deleting(function ($model) {
            if (admin_user()) {
                $model->deleted_user = admin_user()->id;
            } else {
                $model->deleted_user = 0;
            }
            $model->clearCache();
        });
    }

    public function setCache($model): bool
    {
        if ($model->is_default == Base::ENABLE) {
            $data = [
                'id' => $model->id,
                'title' => $model->title,
                'driver' => $model->driver,
                'config' => $model->config,
                'file_size' => $model->file_size,
                'accept' => $model->accept,
                'is_default' => $model->is_default,
            ];

            return Cache::set(self::CACHE_CLOUD_STORAGE_CONFIG_NAME, json_encode($data));
        }

        return false;
    }

    public function getCache(): array
    {
        $data = Cache::get(self::CACHE_CLOUD_STORAGE_CONFIG_NAME);

        return ! empty($data) ? json_decode($data, true) : [];
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_CLOUD_STORAGE_CONFIG_NAME);
    }
}
