<?php

namespace Ennnnny\CloudStorage;

use Slowlyo\OwlAdmin\Extend\Extension;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;

class CloudStorageServiceProvider extends ServiceProvider
{
    protected $menu = [
        [
            'title' => '云存储管理',
            'url' => '/cloud_storage',
            'url_type' => '1',
            'icon' => 'tdesign:object-storage',
        ],
        [
            'parent' => '云存储管理', // 此处父级菜单根据 title 查找
            'title' => '资源管理',
            'url' => '/cloud_storage/resource',
            'url_type' => '1',
            'icon' => 'ant-design:file-protect-outlined',
        ],
        [
            'parent' => '云存储管理', // 此处父级菜单根据 title 查找
            'title' => '存储设置',
            'url' => '/cloud_storage/storage',
            'url_type' => '1',
            'icon' => 'carbon:settings-check',
        ],
    ];

    public function settingForm()
    {
        return null;
    }

    public function boot()
    {
        require_once __DIR__.DIRECTORY_SEPARATOR.'functions.php';
        if (Extension::tableExists()) {
            $this->autoRegister();
            $this->init();
        }
    }
}
