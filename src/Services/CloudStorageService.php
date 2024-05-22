<?php

namespace Slowlyo\CloudStorage\Services;

use Slowlyo\CloudStorage\Models\Base;
use Slowlyo\OwlAdmin\Services\AdminService;
use Slowlyo\CloudStorage\Models\CloudStorage;

/**
 * 存储设置
 *
 * @method CloudStorage getModel()
 * @method CloudStorage|\Illuminate\Database\Query\Builder query()
 */
class CloudStorageService extends AdminService
{
    protected string $modelName = CloudStorage::class;

    /**
     * @param $data
     * @param array $columns
     * @param CloudStorage $model
     * @return int
     * @throws \Exception
     */
    protected function saveData($data, array $columns, CloudStorage $model): int
    {
        foreach ($data as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }
            $model->setAttribute($k, $v);
        }
        return $model->save();
    }

    /**
     * 更新数据
     * @param $primaryKey
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function update($primaryKey, $data): bool
    {
        $columns = $this->getTableColumns();

        $model = $this->query()->whereKey($primaryKey)->first();

        if(isset($data["is_default"]) && $data["is_default"] == 1) {
            //要更新数据
            if($this->query()->where(['is_default'=> Base::ENABLE,'id'=>$model->id])->count() == 0) {
                $this->query()->update(["is_default" => Base::FORBIDDEN]);
            }
        }
        return $this->saveData($data, $columns, $model);
    }

    /**
     * 插入数据
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function store($data): bool
    {
        $columns = $this->getTableColumns();

        $model = $this->getModel();
        if(isset($data["is_default"]) && $data["is_default"] == 1) {
            //要更新数据
            if($this->query()->where('is_default',Base::ENABLE)->count() == 1) {
                $this->query()->update(["is_default" => !$data["is_default"]]);
            }
        }
        return $this->saveData($data, $columns, $model);
    }

    /**
     * 查询数据
     * @return array
     */
    public function list()
    {
        $keyword = request()->keyword;

        $isDefault = request()->is_default;

        $enabled = request()->enabled;

        $query = $this->query()
            ->when(!empty($keyword), function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")->orWhere('description', 'like', "%{$keyword}%");
            })
            ->when(is_numeric($isDefault), function ($query) use ($isDefault) {
                $query->where('is_default', $isDefault);
            })
            ->when(is_numeric($enabled), function ($query) use ($enabled) {
                $query->where('enabled', $enabled);
            });

        $items = (clone $query)->paginate(request()->input('perPage', 20))->items();
        $total = (clone $query)->count();

        return compact('items', 'total');
    }

}
