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

    public function receiver($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->receiver($id);

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * 开始上传文件的准备
     */
    public function startChunk($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->startChunk($id);

            return $this->response()->success($data);
        } catch (\Exception $e) {
            //抛出错误信息
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * 分段上传文件
     */
    public function chunk($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            $data = $this->uploadService->chunk($id);

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * 完成分片上传
     */
    public function finishChunk($id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        try {
            //接取视频
            $data = $this->uploadService->finishChunk($id);

            return $this->response()->success($data);
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }
}
