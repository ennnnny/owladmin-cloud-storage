<?php

namespace Slowlyo\CloudStorage\Http\Controllers;

use Slowlyo\CloudStorage\Services\CloudResourceService;
use Slowlyo\OwlAdmin\Renderers\Form;

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
        $cloudResourceService = new CloudResourceService();
        return $cloudResourceService->list();
    }

    /**
     * 这里就是正常的 crud 的内容
     *
     * @return \Slowlyo\OwlAdmin\Renderers\Page
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
     * @return \Slowlyo\OwlAdmin\Renderers\Page
     * @throws \Exception
     */
    public function page(): \Slowlyo\OwlAdmin\Renderers\Page
    {
        $cloudResourceService = new CloudResourceService();
        return amis()->Page()->body(
            amis()->Flex()->items([
                amis()->Page()->css([
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
                ])->className('w-1/5 mr-5')->body([
                    amis()->Card()->body([
                        amis()->Page()->title(cloud_storage_trans('resource_manage'))->css([
                            '.cxd-Page-header' => [
                                'padding'=> '0'
                            ],'.cxd-Page-title'=>['font-size'=>'16px'],
                        ]),
                        amis()->Divider(),
                        amis()->Nav()->stacked(true)->defaultOpenLevel('2')->expandPosition('after')
                            ->links([
                                [
                                    'label' => cloud_storage_trans('file_type'),
                                    "icon"  => "fas fa-list",
                                    "active"=> true,
                                    'children' => $cloudResourceService->generateIcon(),
                                    'className' => 'nav-type'
                                ]
                            ]),
                    ]),
                    amis()->Card()->body([
                        amis()->Flex()->items([
                            amis()->Page()->body([
                                amis()->Page()->bodyClassName('m-auto')->body(cloud_storage_trans('memory_capacity')),
                                amis()->Page()->bodyClassName('m-auto p-0.5 text-xl text-purple-600')->body($cloudResourceService->getSizeMemory()),
                            ]),
                            amis()->Divider()->className('m-auto')->direction('vertical'),
                            amis()->Page()->body([
                                amis()->Page()->bodyClassName('m-auto')->body(cloud_storage_trans('quantity')),
                                amis()->Page()->bodyClassName('m-auto p-0.5 text-xl text-purple-600')->body($cloudResourceService->count()),
                            ]),
                        ]),
                        amis()->Divider(),
                        amis()->Page()->css([
                            '.chart-box' => [
                                'display' => 'flex',
                                'width' => '105% !important',
                            ]
                        ])->body(
                            amis()->Chart()->className('chart-box')->height('160px')->config([
                                'grid'            => [
                                    'left'   =>  0,
                                    'right'  =>  0,
                                    'top'    =>  0,
                                    'bottom' =>  0,
                                ],
                                'tooltip'         => ['show'=> true,'formatter'=> 'function (params) {console.log(params.data);return `总计：${params.data.value}<br>${params.data.name}:${params.data.size}`}'],
                                'legend'          => [
                                    'show'   => true,
                                    'bottom' => -5,
                                    'icon'   => 'circle',
                                    'itemWidth' => 6,
                                    'itemHeight'=> 6,
                                    'left'=> 'center',
                                    'textStyle' => [
                                        'fontSize' => '8px',
                                    ]
                                ],
                                'series'          => [
                                    [
                                        'type'              => 'pie',
                                        'radius'            => ['40%', '70%'],
                                        'avoidLabelOverlap' => false,
                                        'label'             => [
                                            'show'         => false,
                                            'position'     => 'center',
                                        ],
                                        'emphasis'          => [
                                            'label'         => [
                                                'show'         => true,
                                            ]
                                        ],
                                        'labelLine'         => [
                                            'show'         => false,
                                        ],
                                        'itemStyle'         => ['borderRadius' =>  2,'borderColor' => '#fff', 'borderWidth' => 2],
                                        'data'              => $cloudResourceService->getReport(),
                                    ],
                                ],
                            ])
                        )
                    ]),
                ]),
                $this->list(),
            ])
        );
    }

    /**
     * @throws \Exception
     */
    public function view(): \Slowlyo\OwlAdmin\Renderers\Page
    {
        $cloudResourceService = new CloudResourceService();
        return amis()->Page()->data(['showType'=>'grid','defaultKey'=>'1'])->css([
            '.card-group-page-left' => [
                'padding-top' => '20px',
                'margin-left' => '12px',
                'padding-bottom' => '8px',
            ],
            '.card-group-page-right > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body' => [
                'padding-top' =>'20px',
                'display' => 'flex',
                'padding-bottom' => '8px',
            ],
            '.card-group-page-right > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body > .cxd-Form-item' => [
                'margin-bottom' => '0',
            ]
        ])->body([
            amis()->Flex()->className('bg-white')->items([
                amis()->Page()->id('tabs-list')->className('card-group-page-left w-12')->body([
                    amis()->VanillaAction()->visibleOn('${showType == "grid"}')->icon('fa fa-list')->tooltip(cloud_storage_trans('list'))->tooltipPlacement('top')->onEvent(['click' => ['actions' => [
                                [
                                    'actionType' => 'setValue','componentId' =>
                                    'tabs-list','args' => ['value' => ['showType' => 'list']],
                                ],
                                [
                                    'actionType' => 'changeActiveKey','componentId' =>
                                    'tabs-page','args' => ['activeKey' => 1],
                                ],
                            ],
                        ],
                    ]),
                    amis()->VanillaAction()->visibleOn('${showType == "list"}')->icon('fa fa-border-all')->tooltip(cloud_storage_trans('grid'))->tooltipPlacement('top')->onEvent(['click' => ['actions' => [
                                [
                                    'actionType' => 'setValue','componentId' =>
                                    'tabs-list','args' => ['value' => ['showType' => 'grid']],
                                ],
                                [
                                    'actionType' => 'changeActiveKey','componentId' =>
                                    'tabs-page','args' => ['activeKey' => 2],
                                ],
                            ],
                        ],
                    ]),
                ]),
                amis()->Page()->className('card-group-page-right')->body([
                    amis()->VanillaAction()->label(cloud_storage_trans('upload'))->icon('fa fa-arrow-up-from-bracket')
                        ->actionType('dialog')->level('primary')->dialog(['title'=> '','body' =>
                            [
                                amis()->FileControl('file')->labelWidth('0px')
                                    ->btnLabel(cloud_storage_trans('upload'))
                                    ->accept($cloudResourceService->getAccept())
                                    ->multiple()->drag()->mode('horizontal')
                                    ->joinValues(false)
                                    ->maxLength(env('CLOUD_STORAGE_FILE_MAX_LENGTH',10))
                                    ->maxSize($cloudResourceService->getSize())
                                    ->receiver($this->getUploadReceiverPath())
                                    ->startChunkApi($this->getUploadStartChunkPath())
                                    ->chunkApi($this->getUploadChunkPath())
                                    ->finishChunkApi($this->getUploadFinishChunkPath())
                            ],'actions' => []
                        ]),
                    amis()->TextControl()->name('text')->size('lg')->className('card-group-page-left-search')->labelWidth('0px')->mode('horizontal')->addOn(
                        amis()->VanillaAction()->actionType('submit')
                            ->icon('fas fa-search')->label(cloud_storage_trans('query'))->level('primary')
                    )->placeholder(cloud_storage_trans('keyword_file')),
                ])
            ]),
            amis()->Page()->css([
                '.tabs-view > .cxd-Tabs-linksContainer-wrapper' => [
                    'display' => 'none',
                ],
            ])->body(
                amis()->Tabs()->id('tabs-page')->className('tabs-view')->activeKey('${activeKey|toInt}')->defaultKey('${defaultKey|toInt}')->tabs([
                    // 表格视图
                    amis()->Tab()->className('table-view')->body([
                        amis()->CRUDTable()
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
                                amis()->TableColumn('size', cloud_storage_trans('file_size')),
                                amis()->TableColumn('is_type', cloud_storage_trans('is_type'))->type('mapping')->map([
                                    0 => "<span class='label label-info'>图片</span>",
                                    1 => "<span class='label label-success'>文档</span>",
                                    2 => "<span class='label label-danger'>视频</span>",
                                    3 => "<span class='label label-warning'>音频</span>",
                                    4 => "<span class='label label-default'>其他</span>"
                                ]),
                                $this->rowActions([
                                    $this->rowDeleteButton(true),
                                ])
                            ]),
                    ]),
                    // 卡片视图
                    amis()->Tab()->body([
                        amis()->CRUDCards()
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
                                amis()->Page()->css([
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
                                        'justify-content' => 'center'
                                    ],
                                    '.card-list > .cxd-Card-body > .cxd-Card-field > .cxd-Card-fieldValue > .cxd-Page > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body' => [
                                        'display' => 'flex',
                                        'flex' => '1',
                                        'align-items' => 'center'
                                    ],
                                    '.card-list-text > .cxd-Page-content > .cxd-Page-main' => [
                                        'width'  => '100%',
                                    ],
                                    '.card-list-text > .cxd-Page-content > .cxd-Page-main > .cxd-Page-body > .cxd-TplField > span' => [
                                        'width'  => '100%',
                                        'display' => 'block',
                                        'overflow' => 'hidden',
                                        'white-space' => 'nowrap',
                                        'text-overflow' => 'ellipsis'
                                    ],
                                    '.card-list > .cxd-Card-heading > .cxd-Card-toolbar > .cxd-Checkbox' => [
                                        'padding-left' => '10px'
                                    ],
                                    '.card-link > .cxd-Button' => [
                                        'padding' => '0',
                                    ]
                                ])->body(
                                    amis()->Card()->className('card-list')->body([
                                        amis()->Image()->visibleOn('${is_type == "other"}')->src($cloudResourceService->getIcon('other')),
                                        amis()->Image()->visibleOn('${is_type == "image"}')->name('url')->thumbRatio("16:9")->enlargeAble(true),
                                        amis()->Image()->visibleOn('${is_type == "document"}')->src($cloudResourceService->getIcon('document')),
                                        amis()->Image()->visibleOn('${is_type == "video"}')->src($cloudResourceService->getIcon('video')),
                                        amis()->Image()->visibleOn('${is_type == "audio"}')->src($cloudResourceService->getIcon('audio')),
                                        amis()->Flex()->className('flex-1')->justify('center')->alignItems('center')->items([
                                            amis()->Page()->className('flex-auto card-list-text ml-2 text-xs w-1/4 pr-2')->body('${title}'),
//                                            amis()->DropdownButton()->className('card-link mr-1')->level('link')->icon('fa fa-ellipsis-h')->hideCaret('1')->buttons([
//                                                amis()->VanillaAction()->label(cloud_storage_trans('rename'))->actionType('dialog')->dialog(['title'=> cloud_storage_trans('rename'),'body' => amis()->Page()->body(
//                                                    amis()->TextControl('title', cloud_storage_trans('title'))->required(),
//                                                ),'actions'=> [
//                                                    [
//                                                        "type"       => "button",
//                                                        "actionType" => "close",
//                                                        "label"      => "关闭"
//                                                    ],
//                                                    [
//                                                        'actionType' => 'ajax',
//                                                        'label'      => '确定',
//                                                        'primary'    => true,
//                                                        'type'       => 'button',
//                                                        'api'        => $this->getUpdatePath(),
//                                                        'data'       => ['title'=>'${title}']
//                                                    ]
//                                                ]])->closeOnEsc(true),
//                                                amis()->VanillaAction()->label(cloud_storage_trans('detail'))->actionType('dialog')->dialog(['title'=> cloud_storage_trans('detail'),'body' => amis()->Page()->body([
//                                                    amis()->Image()->name('img')->thumbMode('w-full')->static(),
//                                                    amis()->TextControl('title', cloud_storage_trans('title'))->static(),
//                                                    amis()->TextControl('size', cloud_storage_trans('file_size'))->static(),
//                                                    amis()->TextControl('created_at', __('admin.created_at'))->static(),
//                                                    amis()->TextControl('updated_at', __('admin.updated_at'))->static(),
//                                                ]),'actions'=>[]]),
//                                                amis()->VanillaAction()->label(cloud_storage_trans('download'))->actionType('download')->api('${img}')->header([
//                                                    'Access-Control-Expose-Headers' =>  'Content-Disposition'
//                                                ]),
//                                                amis()->VanillaAction()->label(cloud_storage_trans('delete'))->actionType('ajax')->api($this->getBulkDeletePath())
//                                                    ->label(__('admin.delete'))
//                                                    ->confirmText(__('admin.confirm_delete'))
//                                            ])
                                        ]),
                                    ])
                                )
                            ),
                    ]),
                ])
            )
        ]);
    }

    public function getList(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        $cloudResourceService = new CloudResourceService();
        return $this->response()->success($cloudResourceService->list());
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([]);
    }

}
