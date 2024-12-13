# Owl Admin Extension

## 云存储管理
支持 本地，七牛云、腾讯云、阿里云等OSS云存储功能，支持一键迁移，资源展示等功能。

## 安装

#### zip 下载地址


#### composer

```bash
composer require ennnnny/owladmin-cloud-storage
```

## 使用说明

1. 安装扩展
2. 在扩展管理中启用扩展

## 使用方法

### 配置

需要配置存储方式才能调用

### 调用

```php
use Ennnnny\CloudStorage\Traits\UploadPickerTrait;
class CloudResourceController extends BaseController
{
    use UploadPickerTrait;
    
    // 调用方法
    $this->uploadPicker('icon', __('admin.admin_menu.icon'));
}
```
### 注意事项


