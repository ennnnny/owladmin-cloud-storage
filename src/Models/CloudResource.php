<?php

namespace Slowlyo\CloudStorage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Slowlyo\CloudStorage\Services\CloudResourceService;

class CloudResource extends Base
{
    protected $table = 'suohong_cloud_resource';

    /**
     * 钩子
     * @return void
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

    public function size():Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? formatBytes($value) : 0,
            set: fn($value) => $value,
        );
    }

    public function isType():Attribute
    {
        $cloudResourceService = new CloudResourceService();
        return new Attribute(
            get: fn($value) => $value ? $cloudResourceService::fileType[$value] : 0,
            set: fn($value) => $value,
        );
    }
}
