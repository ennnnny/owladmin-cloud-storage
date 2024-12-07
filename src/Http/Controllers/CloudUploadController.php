<?php

namespace Ennnnny\CloudStorage\Http\Controllers;

use Ennnnny\CloudStorage\Services\CloudUploadService;

class CloudUploadController extends BaseController
{
    protected CloudUploadService $uploadService;

    public function __construct()
    {
        parent::__construct();
        $this->uploadService = new CloudUploadService;
    }

    public function receiver(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->receiver();

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * @Desc: 开始上传文件的准备
     *
     * @Author: Keivn
     *
     * @Date: 2023/9/1 13:45
     */
    public function startChunk(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->startChunk();

            return $this->response()->success($data);
        } catch (\Exception $e) {
            //抛出错误信息
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * @Desc: 分段上传文件
     *
     * @Author: Keivn
     *
     * @Date: 2023/9/1 14:47
     */
    public function chunk(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->chunk();

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * @esc: 完成分片上传
     *
     * @Author: Keivn
     *
     * @Date: 2023/9/6 9:36
     */
    public function finishChunk(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            //接取视频
            $data = $this->uploadService->finishChunk();

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }
}
