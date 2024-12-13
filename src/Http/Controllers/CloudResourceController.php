<?php

namespace Ennnnny\CloudStorage\Http\Controllers;

use Ennnnny\CloudStorage\Services\CloudResourceService;
use Ennnnny\CloudStorage\Services\CloudStorageService;
use Slowlyo\OwlAdmin\Renderers\Form;

/**
 * @property CloudResourceService $service
 */
class CloudResourceController extends BaseController
{
    protected string $serviceName = CloudResourceService::class;

    /**
     * @throws \Exception
     */
    public function index(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        return $this->response()->success($this->page());
    }

    public function getData(): array
    {
        return $this->service->list();
    }

    /**
     * 这里就是正常的 crud 的内容
     *
     * @throws \Exception
     */
    public function list(): \Slowlyo\OwlAdmin\Renderers\Page
    {
        $page = amis()->Page()->data($this->getData())->body(
            $this->view()
        );

        return $this->baseList($page);
    }

    /**
     * 页面css
     *
     * @return array[]
     */
    private function pageCss(string $type = 'page'): array
    {
        $data = [];
        switch ($type) {
            case 'page':
                $data = [
                    '.nav-type > .cxd-Nav-Menu-submenu-title > .cxd-Nav-Menu-item-wrap > .cxd-Nav-Menu-item-link .nav-icon-img > .cxd-Nav-Menu-item-wrap > .cxd-Nav-Menu-item-link' => [
                        'display' => 'flex',
                        'align-items' => 'center',
                    ],
                    '.nav-type > .cxd-Nav-Menu > .cxd-Nav-Menu-item-tooltip-wrap > .cxd-Nav-Menu-item > .cxd-Nav-Menu-item-wrap > .cxd-Nav-Menu-item-link' => [
                        'display' => 'flex',
                        'align-items' => 'center',
                    ],
                    '.nav-type > .cxd-Nav-Menu-submenu-title > .cxd-Nav-Menu-item-wrap > .cxd-Nav-Menu-item-link > .cxd-Nav-Menu-item-icon' => [
                        'font-size' => '18px',
                    ],
                    '.nav-icon-img > .cxd-Nav-Menu-item-wrap > .cxd-Nav-Menu-item-link > .cxd-Nav-Menu-item-icon >.cxd-Icon' => [
                        'width' => '24px',
                        'height' => 'auto',
                    ],
                    '.nav-icon-img:hover' => [
                        'background' => 'var(--colors-brand-10)',
                    ],
                ];
                break;
            case 'view':
                $data = [
                    '.card-group-page-left' => [
                        'padding-top' => '20px',
                        'margin-left' => '12px',
                        'padding-bottom' => '8px',
                    ],
                    '.card-group-page-right > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body' => [
                        'padding-top' => '20px',
                        'display' => 'flex',
                        'padding-bottom' => '8px',
                    ],
                    '.card-group-page-right > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body > .cxd-Form-item' => [
                        'margin-bottom' => '0',
                    ],
                ];
                break;
            case 'card':
                $data = [
                    '.card-list:hover' => [
                        'background' => 'var(--colors-brand-10)',
                    ],
                    '.card-list > .cxd-Card-heading' => [
                        'padding' => '0',
                        'display' => 'inline-block',
                        'position' => 'absolute',
                        'z-index' => '99',
                    ],
                    '.card-list > .cxd-Card-heading > .cxd-Card-toolbar' => [
                        'margin-left' => '0',
                        'text-align' => 'left',
                    ],
                    '.card-list > .cxd-Card-body' => [
                        'padding' => '0',
                    ],
                    '.card-list > .cxd-Card-body > .cxd-Card-field > .cxd-Card-fieldValue > .cxd-ImageField' => [
                        'display' => 'flex',
                        'justify-content' => 'center',
                    ],
                    '.card-list > .cxd-Card-body > .cxd-Card-field > .cxd-Card-fieldValue > .cxd-Page > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body' => [
                        'display' => 'flex',
                        'flex' => '1',
                        'align-items' => 'center',
                    ],
                    '.card-list-text > .cxd-Page-content > .cxd-Page-main' => [
                        'width' => '100%',
                    ],
                    '.card-list-text > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body > .cxd-TplField > span' => [
                        'width' => '100%',
                        'display' => 'block',
                        'overflow' => 'hidden',
                        'white-space' => 'nowrap',
                        'text-overflow' => 'ellipsis',
                    ],
                    '.card-list > .cxd-Card-heading > .cxd-Card-toolbar > .cxd-Checkbox' => [
                        'padding-left' => '10px',
                    ],
                    '.card-link > .cxd-Button' => [
                        'padding' => '0',
                    ],
                    '.card-list > .cxd-Card-body > .cxd-Card-field > .cxd-Card-fieldValue > .cxd-ImageField > .cxd-Image' => [
                        'width' => '9em',
                        'height' => '9em',
                        'display' => 'flex',
                        'align-items' => 'center',
                    ],
                ];
                break;
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function page(): \Slowlyo\OwlAdmin\Renderers\Page
    {
        return amis()->Page()->body(
            amis()->Flex()->items([
                amis()->Page()->css($this->pageCss('page'))->className('w-1/5 mr-5')->body([
                    amis()->Card()->body([
                        amis()->Page()->title(cloud_storage_trans('resource_manage'))->css([
                            '.cxd-Page-header' => [
                                'padding' => '0',
                            ], '.cxd-Page-title' => ['font-size' => '16px'],
                        ]),
                        amis()->Divider(),
                        amis()->Nav()->stacked(true)->defaultOpenLevel('2')->expandPosition('after')
                            ->links([
                                [
                                    'label' => cloud_storage_trans('file_type'),
                                    'icon' => 'fas fa-list',
                                    'active' => true,
                                    'children' => $this->service->generateIcon(),
                                    'className' => 'nav-type',
                                ],
                            ]),
                    ]),
                    amis()->Card()->body([
                        amis()->Flex()->items([
                            amis()->Page()->body([
                                amis()->Page()->bodyClassName('m-auto')->body(cloud_storage_trans('memory_capacity')),
                                amis()->Page()->bodyClassName('m-auto p-0.5 text-xl text-purple-600')->body($this->service->getSizeMemory()),
                            ]),
                            amis()->Divider()->className('m-auto')->direction('vertical'),
                            amis()->Page()->body([
                                amis()->Page()->bodyClassName('m-auto')->body(cloud_storage_trans('quantity')),
                                amis()->Page()->bodyClassName('m-auto p-0.5 text-xl text-purple-600')->body($this->service->getCount()),
                            ]),
                        ]),
                        amis()->Divider(),
                        amis()->Page()->css([
                            '.chart-box' => [
                                'display' => 'flex',
                                'width' => '105% !important',
                            ],
                        ])->body(
                            $this->chart()
                        ),
                    ]),
                ]),
                $this->list(),
            ])
        );
    }

    /**
     * 圆饼
     */
    private function chart(): \Slowlyo\OwlAdmin\Renderers\Chart
    {
        return amis()->Chart()->className('chart-box')->height('160px')->config([
            'grid' => [
                'left' => 0,
                'right' => 0,
                'top' => 0,
                'bottom' => 0,
            ],
            'tooltip' => ['show' => true, 'formatter' => 'function (params) {console.log(params.data);return `总计：${params.data.value}<br>${params.data.name}:${params.data.size}`}'],
            'legend' => [
                'show' => true,
                'bottom' => -5,
                'icon' => 'circle',
                'itemWidth' => 6,
                'itemHeight' => 6,
                'left' => 'center',
                'textStyle' => [
                    'fontSize' => '8px',
                ],
            ],
            'series' => [
                [
                    'type' => 'pie',
                    'radius' => ['40%', '70%'],
                    'avoidLabelOverlap' => false,
                    'label' => [
                        'show' => false,
                        'position' => 'center',
                    ],
                    'emphasis' => [
                        'label' => [
                            'show' => true,
                        ],
                    ],
                    'labelLine' => [
                        'show' => false,
                    ],
                    'itemStyle' => ['borderRadius' => 2, 'borderColor' => '#fff', 'borderWidth' => 2],
                    'data' => $this->service->getReport(),
                ],
            ],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function view(): \Slowlyo\OwlAdmin\Renderers\Page
    {
        return amis()->Page()->data(['showType' => 'grid', 'defaultKey' => '1'])->css($this->pageCss('view'))->body([
            amis()->Flex()->className('bg-white')->items([
                amis()->Page()->id('tabs-list')->className('card-group-page-left w-12')->body([
                    amis()->VanillaAction()->visibleOn('${showType == "grid"}')->icon('fa fa-list')->tooltip(cloud_storage_trans('list'))->tooltipPlacement('top')->onEvent(['click' => ['actions' => [
                        [
                            'actionType' => 'setValue', 'componentId' => 'tabs-list', 'args' => ['value' => ['showType' => 'list']],
                        ],
                        [
                            'actionType' => 'changeActiveKey', 'componentId' => 'tabs-page', 'args' => ['activeKey' => 1],
                        ],
                    ],
                    ],
                    ]),
                    amis()->VanillaAction()->visibleOn('${showType == "list"}')->icon('fa fa-border-all')->tooltip(cloud_storage_trans('grid'))->tooltipPlacement('top')->onEvent(['click' => ['actions' => [
                        [
                            'actionType' => 'setValue', 'componentId' => 'tabs-list', 'args' => ['value' => ['showType' => 'grid']],
                        ],
                        [
                            'actionType' => 'changeActiveKey', 'componentId' => 'tabs-page', 'args' => ['activeKey' => 2],
                        ],
                    ],
                    ],
                    ]),
                ]),
                amis()->Page()->className('card-group-page-right')->body([
                    amis()->VanillaAction()->label(cloud_storage_trans('upload'))->icon('fa fa-arrow-up-from-bracket')
                        ->actionType('dialog')->level('primary')->dialog([
                            'title' => '',
                            'body' => [
                                amis()->SelectControl('storage_id', '存储设置')->selectFirst()
                                    ->options(CloudStorageService::make()->getStorageOptions())->required(),
                                amis()->FileControl('file')->labelWidth('0px')
                                    ->btnLabel(cloud_storage_trans('upload'))
                                    ->accept($this->service->getAccept())
                                    ->multiple()->drag()->mode('horizontal')
                                    ->joinValues(false)
                                    ->maxLength(env('CLOUD_STORAGE_FILE_MAX_LENGTH', 10))
//                                    ->maxSize($this->service->getSize())
                                    ->autoUpload(false)
                                    ->receiver($this->getUploadReceiverPath().'/${storage_id}')
                                    ->startChunkApi($this->getUploadStartChunkPath().'/${storage_id}')
                                    ->chunkApi($this->getUploadChunkPath().'/${storage_id}')
                                    ->finishChunkApi($this->getUploadFinishChunkPath().'/${storage_id}')
                                    ->visibleOn('${storage_id != null}'),
                            ],
                            'actions' => [],
                        ])->reload('window'),
                    amis()->TextControl()->name('text')->size('lg')->className('card-group-page-left-search')->labelWidth('0px')->mode('horizontal')->addOn(
                        amis()->VanillaAction()->actionType('submit')
                            ->icon('fas fa-search')->label(cloud_storage_trans('query'))->level('primary')
                    )->placeholder(cloud_storage_trans('keyword_file')),
                ]),
            ]),
            amis()->Page()->css([
                '.tabs-view > .cxd-Tabs-linksContainer-wrapper' => [
                    'display' => 'none',
                ],
            ])->body(
                amis()->Tabs()->id('tabs-page')->className('tabs-view')->activeKey('${activeKey|toInt}')->defaultKey('${defaultKey|toInt}')->tabs([
                    // 表格视图
                    amis()->Tab()->className('table-view')->body(
                        $this->CRUDTablePage()
                    ),
                    // 卡片视图
                    amis()->Tab()->body(
                        $this->CRUDCardsPage()
                    ),
                ])
            ),
        ]);
    }

    private function CRUDTablePage(): \Slowlyo\OwlAdmin\Renderers\CRUDTable
    {
        return amis()->CRUDTable()
            ->perPage(20)
            ->affixHeader(false)
            ->filterTogglable()
            ->filterDefaultVisible(true)
            ->api($this->getResourceListPath())
            ->bulkActions([$this->bulkDeleteButton()])
            ->perPageAvailable([10, 20, 30, 50, 100, 200])
            ->footerToolbar(['switch-per-page', 'statistics', 'pagination'])
            ->columns([
                amis()->TableColumn('title', cloud_storage_trans('title')),
                amis()->TableColumn('extension', '后缀'),
                amis()->TableColumn('size', cloud_storage_trans('file_size')),
                amis()->TableColumn('is_type', cloud_storage_trans('is_type'))->type('mapping')->map([
                    'image' => "<span class='label label-info'>图片</span>",
                    'document' => "<span class='label label-success'>文档</span>",
                    'video' => "<span class='label label-danger'>视频</span>",
                    'audio' => "<span class='label label-warning'>音频</span>",
                    'other' => "<span class='label label-default'>其他</span>",
                ]),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
                $this->rowActions([
                    $this->rowDeleteButton(),
                ]),
            ]);
    }

    private function CRUDCardsPage(): \Slowlyo\OwlAdmin\Renderers\CRUDCards
    {
        return amis()->CRUDCards()
            ->perPage(40)
            ->affixHeader(false)
            ->filterTogglable()
            ->filterDefaultVisible(true)
            ->api($this->getResourceListPath())
            ->bulkActions([$this->bulkDeleteButton()])
            ->set('columnsCount', 8)
            ->perPageAvailable([40, 80, 120, 160, 200, 240])
            ->footerToolbar(['switch-per-page', 'statistics', 'pagination'])
            ->card(
                amis()->Page()->css($this->pageCss('card'))->body(
                    amis()->Card()->className('card-list')->body([
                        amis()->Image()->visibleOn('${is_type == "other"}')->src($this->service->getIcon('/image/file-type/other.png')),
                        amis()->Image()->visibleOn('${is_type == "image"}')->name('url.value')->thumbRatio('16:9')->enlargeAble(true),
                        amis()->Image()->visibleOn('${is_type == "document"}')->src($this->service->getIcon('/image/file-type/document.png'))->onEvent(['click' => ['actions' => [
                            [
                                'actionType' => 'dialog', 'dialog' => [
                                    'title' => cloud_storage_trans('file_preview'),
                                    'body' => amis()->Page()->body([
                                        amis()->Page()->visibleOn('${event.data.extension == "pdf"}')->body(
                                            amis('pdf-viewer')->id('pdf-viewer')->src('${event.data.url.value}')->width('450'),
                                        ),
                                        amis()->Page()->visibleOn('${event.data.extension == "docx" || event.data.extension == "xlsx" || event.data.extension == "csv" || event.data.extension == "tsv"}')->body(
                                            amis('office-viewer')->id('office-viewer-page')->wordOptions([
                                                'page' => true,
                                            ])->src('${event.data.url.value}')->width('450')
                                        ),
                                    ]),
                                ],
                            ],
                        ],
                        ],
                        ]),
                        amis()->Image()->visibleOn('${is_type == "video"}')->src($this->service->getIcon('/image/file-type/video.png'))->onEvent(['click' => ['actions' => [
                            [
                                'actionType' => 'dialog', 'dialog' => [
                                    'title' => cloud_storage_trans('video_play'),
                                    'body' => amis()->Page()->body(
                                        amis()->Video()->src('${event.data.url.value}')->autoPlay(true)
                                    ),
                                ],
                            ],
                        ],
                        ],
                        ]),
                        amis()->Image()->visibleOn('${is_type == "audio"}')->src($this->service->getIcon('/image/file-type/audio.png')),
                        amis()->Flex()->className('flex-1')->justify('center')->alignItems('center')->items([
                            amis()->Page()->className('flex-auto card-list-text ml-2 text-xs w-1/4 pr-2')->body('${title}'),
                            amis()->DropdownButton()->className('card-link mr-1')->level('link')->icon('fa fa-ellipsis-h')->hideCaret('1')->buttons([
                                amis()->VanillaAction()->label(cloud_storage_trans('rename'))->actionType('dialog')->dialog(['title' => cloud_storage_trans('rename'), 'body' => amis()->Page()->body(
                                    amis()->TextareaControl('title', cloud_storage_trans('title'))->required(),
                                ), 'actions' => [
                                    [
                                        'type' => 'button',
                                        'actionType' => 'close',
                                        'label' => cloud_storage_trans('close'),
                                    ],
                                    [
                                        'actionType' => 'ajax',
                                        'label' => cloud_storage_trans('confirm'),
                                        'primary' => true,
                                        'type' => 'button',
                                        'api' => [
                                            'url' => $this->updateResourcePath(),
                                            'method' => 'PUT',
                                            'data' => [
                                                'id' => '${id}',
                                                'title' => '${title}',
                                            ],
                                            'messages' => [
                                                'success' => __('admin.save_success'),
                                                'failed' => __('admin.save_failed'),
                                            ],
                                        ],
                                        'close' => true,
                                    ],
                                ]])->closeOnEsc(true),
                                amis()->VanillaAction()->label(cloud_storage_trans('detail'))->actionType('dialog')->dialog(['title' => cloud_storage_trans('detail'), 'body' => amis()->Page()->body([
                                    amis()->Image()->visibleOn('${is_type == "other"}')->src($this->service->getIcon('/image/file-type/other.png')),
                                    amis()->Image()->visibleOn('${is_type == "image"}')->name('url.value')->thumbRatio('16:9')->enlargeAble(true),
                                    amis()->Image()->visibleOn('${is_type == "document"}')->src($this->service->getIcon('/image/file-type/document.png')),
                                    amis()->Image()->visibleOn('${is_type == "video"}')->src($this->service->getIcon('/image/file-type/video.png')),
                                    amis()->Image()->visibleOn('${is_type == "audio"}')->src($this->service->getIcon('/image/file-type/audio.png')),
                                    amis()->TextControl('title', cloud_storage_trans('title'))->static(),
                                    amis()->TextControl('extension', '后缀')->static(),
                                    amis()->TextControl('size', cloud_storage_trans('file_size'))->static(),
                                    amis()->TextControl('created_at', __('admin.created_at'))->static(),
                                    amis()->TextControl('updated_at', __('admin.updated_at'))->static(),
                                ]), 'actions' => []]),
                                //                                amis()->VanillaAction()->label(cloud_storage_trans('download'))->actionType('download')->api($this->downloadResourcePath()),
                                amis()->DialogAction()->label(__('admin.delete'))->dialog(
                                    amis()->Dialog()
                                        ->title(__('admin.delete'))
                                        ->className('py-2')
                                        ->actions([
                                            amis()->Action()->actionType('cancel')->label(__('admin.cancel')),
                                            amis()->Action()->actionType('submit')->label(__('admin.delete'))->level('danger'),
                                        ])
                                        ->body([
                                            amis()->Form()->wrapWithPanel(false)->api($this->deleteResourcePath())->body([
                                                amis()->Tpl()->className('py-2')->tpl(__('admin.confirm_delete')),
                                            ]),
                                        ])
                                ),
                            ]),
                        ]),
                    ])
                )
            );
    }

    public function getList(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        return $this->response()->success($this->service->list());
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([]);
    }

    public function download($id)
    {
        $detail = $this->service->getDetail($id);
        if (empty($detail)) {
            return $this->response()->fail(cloud_storage_trans('download_failed'));
        }

        //        // 设置响应头
        //        $headers = [
        //            'Content-Type' => Storage::disk('public')->mimeType($detail->url['path']),
        //            'Content-Disposition' => 'attachment; filename="'.urlencode($detail->title).'.'.$detail->extension.'"',
        //        ];
        return $this->response()->success($detail);
        // 返回文件作为响应
        //        return Response::make($detail->url, 200, $headers);
        //        return response()->download($detail->url['value'], $detail->title, [
        //            'Content-Disposition' => 'attachment; filename="'.$detail->title.'"'
        //        ]);
    }
}
