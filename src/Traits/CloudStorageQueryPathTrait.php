<?php

namespace Slowlyo\CloudStorage\Traits;

trait CloudStorageQueryPathTrait
{
    /**
     * 简单上传
     * @return string
     */
    public function getUploadReceiverPath(): string
    {
        return admin_url('cloud_storage/upload/receiver');
    }

    /**
     * 开始上传文件的准备
     * @return string
     */
    public function getUploadStartChunkPath(): string
    {
        return admin_url('cloud_storage/upload/startChunk');
    }

    /**
     * 分段上传文件
     * @return string
     */
    public function getUploadChunkPath(): string
    {
        return admin_url('cloud_storage/upload/chunk');
    }

    /**
     * 完成分片上传
     * @return string
     */
    public function getUploadFinishChunkPath(): string
    {
        return admin_url('cloud_storage/upload/finishChunk');
    }

    public function getResourceListPath(): string
    {
        return admin_url('get/cloud_storage/resource/getList');
    }
}
