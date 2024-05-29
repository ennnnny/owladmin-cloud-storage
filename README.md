## OwlAdmin 数据字典

## 效果

增加数据字典管理功能

## 安装

#### zip 下载地址

[https://github.com/szsuohong/cloud-storage/archive/refs/tags/v.0.1.0.zip](https://github.com/szsuohong/cloud-storage/archive/refs/tags/v.0.1.0.zip)

#### composer

```bash
composer require szsuohong/cloud-storage
```

## 使用说明

1. 安装扩展
2. 在扩展管理中启用扩展

## 使用方法

### 配置

需要配置存储方式才能调用

### 调用

```php
use Slowlyo\CloudStorage\Traits\UploadPickerTrait;
class CloudResourceController extends BaseController
{
    use UploadPickerTrait;
}
// 调用方法
 $this->uploadPicker('icon', __('admin.admin_menu.icon')),
```
### 注意事项


