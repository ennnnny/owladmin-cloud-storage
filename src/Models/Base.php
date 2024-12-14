<?php

namespace Ennnnny\CloudStorage\Models;

use Ennnnny\CloudStorage\Services\CloudUploadService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

class Base extends Model
{
    use SoftDeletes;

    const ENABLE = 1;

    const FORBIDDEN = 0;

    const CACHE_CLOUD_STORAGE_CONFIG_NAME = 'cache_cloud_storage';

    public function img(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? $this->getCloudStoragePath($value) : '',
            set: fn ($value) => $value
        );
    }

    /**
     * 获取加密链接
     *
     * @throws \Exception
     */
    public function getCloudStoragePath($value, $id = null): string
    {
        $cloudUploadService = new CloudUploadService;

        return $cloudUploadService->signUrl($value, $id);
    }
}
