<?php

namespace Ennnnny\CloudStorage\Services;

use Ennnnny\CloudStorage\Models\Base;
use Ennnnny\CloudStorage\Models\CloudStorage;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 存储设置
 *
 * @method CloudStorage getModel()
 * @method CloudStorage|\Illuminate\Database\Query\Builder query()
 */
class CloudStorageService extends AdminService
{
    protected string $modelName = CloudStorage::class;

    public function saving(&$data, $primaryKey = '')
    {
        if (filled($data)) {
            $data['description'] = $data['description'] ?? '';
            $data['extension'] = $data['extension'] ?? '';
        }
    }

    public function saved($model, $isEdit = false)
    {
        if ($model->is_default == Base::ENABLE) {
            $this->query()->where('id', '!=', $model->id)->update(['is_default' => Base::FORBIDDEN]);
        }
    }

    public function getStorageOptions()
    {
        $data = $this->query()->where('enabled', 1)->get(['id', 'title'])->toArray();
        $res = [];
        foreach ($data as $datum) {
            $res[] = ['label' => $datum['title'], 'value' => $datum['id']];
        }

        return $res;
    }

    /**
     * 查询数据
     *
     * @return array
     */
    public function list()
    {
        $keyword = request()->keyword;

        $isDefault = request()->is_default;

        $enabled = request()->enabled;

        $query = $this->query()
            ->when(! empty($keyword), function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")->orWhere('description', 'like', "%{$keyword}%");
            })
            ->when(is_numeric($isDefault), function ($query) use ($isDefault) {
                $query->where('is_default', $isDefault);
            })
            ->when(is_numeric($enabled), function ($query) use ($enabled) {
                $query->where('enabled', $enabled);
            });

        $items = (clone $query)->paginate(request()->input('perPage', 20))->items();
        foreach ($items as &$item) {
            if (filled($item)) {
                switch ($item->driver) {
                    case 'local':
                        $driver_str = '本地存储';
                        break;
                    case 'oss':
                        $driver_str = '阿里云OSS';
                        break;
                    case 'cos':
                        $driver_str = '腾讯云COS';
                        break;
                    case 'kodo':
                        $driver_str = '七牛云KODO';
                        break;
                    default:
                        $driver_str = '';
                }
                $item->setAttribute('driver_str', $driver_str);
            }
        }
        $total = (clone $query)->count();

        return compact('items', 'total');
    }
}
