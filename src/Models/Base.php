<?php

namespace Slowlyo\CloudStorage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\CloudStorage\Services\CloudUploadService;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

class Base extends Model
{
    use SoftDeletes;

    const ENABLE = 1;
    const FORBIDDEN = 0;

    const CACHE_CLOUD_STORAGE_CONFIG_NAME = "cache_suohong_cloud_storage";

    public function img(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? $this->getCloudStoragePath($value) : '',
            set: fn($value) => $value
        );
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? [
                'path' => $value,
                'value'  => $this->getCloudStoragePath($value)
            ] : [],
            set: fn($value) => $value
        );
    }

    /**
     * 获取加密链接
     * @throws \Exception
     */
    public function getCloudStoragePath($value): string
    {
        $cloudUploadService = new CloudUploadService();
        return $cloudUploadService->signUrl($value);
    }
}
