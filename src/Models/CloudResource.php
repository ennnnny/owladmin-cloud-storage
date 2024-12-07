<?php

namespace Ennnnny\CloudStorage\Models;

use Ennnnny\CloudStorage\Services\CloudResourceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class CloudResource extends Base
{
    use HasUlids;

    protected $table = 'admin_cloud_resource';

    /**
     * 钩子
     */
    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_user = admin_user()->id;
        });
        static::deleting(function ($model) {
            $model->deleted_user = admin_user()->id;
        });
    }

    public function size(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value ? formatBytes($value) : 0,
            set: fn ($value) => $value,
        );
    }

    public function isType(): Attribute
    {
        $cloudResourceService = new CloudResourceService;

        return new Attribute(
            get: fn ($value) => $value ? $cloudResourceService::fileType[$value] : 0,
            set: fn ($value) => $value,
        );
    }
}
