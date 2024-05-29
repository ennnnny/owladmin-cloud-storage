<?php

namespace Slowlyo\CloudStorage\Http\Controllers;

use Slowlyo\CloudStorage\Services\CloudStorageService;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Page;

class CloudStorageController extends BaseController
{
    protected string $serviceName = CloudStorageService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                $this->createButton(true),
                ...$this->baseHeaderToolBar(),
            ])
            ->filter(
                $this->baseFilter()->body([
                    amis()->TextControl('keyword', __('admin.keyword'))
                        ->size('lg')
                        ->placeholder("请输入关键词"),
                    amis()->SelectControl('is_default',cloud_storage_trans('is_default'))
                        ->size('lg')
                        ->joinValues(false)
                        ->extractValue()
                        ->clearable()
                        ->options(cloud_storage_trans('is_default_select')),
                    amis()->SelectControl('enabled',cloud_storage_trans('status'))
                        ->size('lg')
                        ->joinValues(false)
                        ->extractValue()
                        ->clearable()
                        ->options(cloud_storage_trans('status_select')),
                ])
            )
            ->quickSaveApi($this->getQuickEditPath())
            ->bulkActions([$this->bulkDeleteButton()->reload('window')])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('title', cloud_storage_trans('title')),
                amis()->TableColumn('driver', cloud_storage_trans('driver')),
                amis()->TableColumn('description', cloud_storage_trans('description')),
                amis()->TableColumn('sort', cloud_storage_trans('sort')),
                amis()->SwitchControl('is_default', cloud_storage_trans('is_default'))->onText(__('admin.yes'))
                    ->offText(__('admin.no'))
                    ->value(1)
                    ->trueValue(1)
                    ->falseValue(0),
                amis()->SwitchControl('enabled', cloud_storage_trans('status'))->onText(__('admin.yes'))
                    ->offText(__('admin.no'))
                    ->value(1)
                    ->trueValue(1)
                    ->falseValue(0),
                amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime'),
                $this->rowActions([
                    $this->rowEditButton(true),
                    $this->rowDeleteButton(true),
                ])
            ]);

        return $this->baseList($crud);
    }


    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
            amis()->HiddenControl('id','ID'),
            amis()->TextControl('title', '名称')->required(),
            amis()->SelectControl('driver', '驱动')->disabled($isEdit)->options(
                cloud_storage_trans('driver_select')
            )->value('local')->required(),
            // 本地
            amis()->Container()->hiddenOn('${driver!="local"}')->body([
                amis()->TextControl('config.domain',  cloud_storage_trans('domain'))->required(),
                amis()->TextControl('config.root', cloud_storage_trans('root'))->desc(cloud_storage_trans('root_desc'))->required(),
            ]),
            // OSS 阿里云对象存储
            amis()->Container()->hiddenOn('${driver!="oss"}')->body([
                amis()->TextControl('config.root', cloud_storage_trans('prefix'))->desc(cloud_storage_trans('prefix_desc')),
                amis()->TextControl('config.access_key', cloud_storage_trans('access_key'))->required(),
                amis()->TextControl('config.secret_key', cloud_storage_trans('secret_key'))->required()->type('input-password'),
                amis()->TextControl('config.endpoint', cloud_storage_trans('endpoint'))->required()->desc(cloud_storage_trans('endpoint')),
                amis()->TextControl('config.bucket', cloud_storage_trans('bucket'))->required(),
            ]),
            // COS 腾讯云对象存储
            amis()->Container()->hiddenOn('${driver!="cos"}')->body([
                amis()->TextControl('config.secret_id', cloud_storage_trans('secret_id'))->required(),
                amis()->TextControl('config.secret_key', cloud_storage_trans('secret_key'))->required()->type('input-password'),
                amis()->TextControl('config.bucket', cloud_storage_trans('bucket'))->desc(cloud_storage_trans('bucket_desc'))->required(),
                amis()->TextControl('config.region', cloud_storage_trans('region'))->desc(cloud_storage_trans('region_desc'))->required(),
                amis()->TextControl('config.domain',  cloud_storage_trans('domain')),
            ]),
            // 七牛云存储
//            amis()->Container()->hiddenOn('${driver!="kodo"}')->body([
//                amis()->TextControl('config.access_key', cloud_storage_trans('access_key'))->required(),
//                amis()->TextControl('config.secret_key', cloud_storage_trans('secret_key'))->required()->type('input-password'),
//                amis()->TextControl('config.bucket', cloud_storage_trans('bucket'))->desc(cloud_storage_trans('bucket_desc'))->required(),
//                amis()->TextControl('config.domain',  cloud_storage_trans('domain')),
//            ]),
            amis()->Page()->className(['m-3']),
            amis()->NumberControl('file_size', cloud_storage_trans('file_size'))->desc(cloud_storage_trans('file_size_desc')),
            amis()->TextareaControl('accept', cloud_storage_trans('accept'))->desc(cloud_storage_trans('accept_desc')),
            amis()->TextareaControl('description', cloud_storage_trans('description')),
            amis()->TextControl('sort', cloud_storage_trans('sort'))->value(1000),
            amis()->SwitchControl('is_default', cloud_storage_trans('is_default'))->value(0),
            amis()->SwitchControl('enabled', cloud_storage_trans('status'))->value(0),
        ])->onEvent([
            'submitSucc' => [
                'actions' => [
                    'actionType' => 'custom',
                    'script'     => 'window.$owl.refreshRoutes()',
                ],
            ],
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
