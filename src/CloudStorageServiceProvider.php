<?php

namespace Slowlyo\CloudStorage;

use Slowlyo\OwlAdmin\Extend\Extension;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;

class CloudStorageServiceProvider extends ServiceProvider
{
    protected $menu = [
        [
            'title'    => '资源云存储',
            'url'      => '/cloud_storage',
            'url_type' => '1',
            'icon'     => 'tdesign:object-storage',
        ],
        [
            'parent'   => '资源云存储', // 此处父级菜单根据 title 查找
            'title'    => '资源管理',
            'url'      => '/cloud_storage/resource',
            'url_type' => '1',
            'icon'     => 'ant-design:file-protect-outlined',
        ],
        [
            'parent'   => '资源云存储', // 此处父级菜单根据 title 查找
            'title'    => '存储设置',
            'url'      => '/cloud_storage/storage',
            'url_type' => '1',
            'icon'     => 'carbon:settings-check',
        ]
    ];

    public function install()
    {
        parent::install();
//        if(!FilesystemConfig::query()->where('key','local')->first()){
//            FilesystemConfig::query()->insert([
//                'name'=>'默认存储',
//                'desc'=>'系统默认本地存储',
//                'key'=>'local',
//                'driver'=>'local',
//                'config'=>json_encode([
//                    'driver'=>'local',
//                    'root'=>'uploads',
//                    'throw'=>false
//                ]),
//                'created_at'=>date('Y-m-d H:i:s'),
//                'updated_at'=>date('Y-m-d H:i:s'),
//            ]);
//        }
    }

    public function boot()
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php');
        if (Extension::tableExists()) {
            $this->autoRegister();
            $this->init();
        }
    }
}
