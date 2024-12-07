<?php

namespace Ennnnny\CloudStorage\Services;

use Ennnnny\CloudStorage\Factory\CloudStorage\CloudStorageFactory;
use Ennnnny\CloudStorage\Models\CloudStorage;

class CloudUploadService
{
    /**
     * 配置信息
     *
     * @throws \Exception
     */
    public function config(): object
    {
        $cloudStorage = new CloudStorage;
        $data = $cloudStorage->getCache();
        if (empty($data)) {
            throw new \Exception(cloud_storage_trans('no_default_storage_settings'));
        }

        return (object) $data;
    }

    /**
     * 上传时，插入数据
     *
     * @throws \Exception
     */
    public function store(array $data): void
    {
        if (! empty($data)) {
            $arr1 = explode('.', $data['path']);
            $length1 = count($arr1);
            $arr2 = explode('/', $arr1[0]);
            $title = end($arr2);
            //需要转换
            $type = isset($data['path']) ? getAccept($data['path']) : 'other';
            $cloudResourceService = new CloudResourceService;
            $cloudResourceService->store([
                'title' => $title,
                'size' => $data['size'] ?? 0,
                'url' => $data['path'],
                'is_type' => array_flip($cloudResourceService::fileType)[$type],
                'storage_id' => $cloudResourceService->getStorageId(),
                'extension' => $arr1[$length1 - 1] ?? null,
            ]);
        }
    }

    /**
     * 简单上传
     *
     * @throws \Exception
     */
    public function receiver(): mixed
    {
        try {
            $file = request()->file('file');
            if (! $file) {
                throw new \Exception(cloud_storage_trans('no_file'));
            }
            //文件限制大小  更换最后的数字调整文件上传的大小
            $this->getSize($file);
            $fileName = $file->getClientOriginalName();
            $object = $this->generateFileName($fileName);
            $filePath = $file->getRealPath();
            $size = $file->getSize();
            // 配置信息
            $config = $this->config();
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($config);
            $data = $cloudStorageFactory->receiver($object, $filePath);
            // 插入数据库
            $this->store(array_merge($data, ['size' => $size]));

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 开始上传文件的准备
     *
     * @param  $fileName
     *
     * @throws \Exception
     */
    public function startChunk(): array
    {
        //第一步 ： 初始化一个分片上传事件，获取uploadId。
        $fileName = request()->name;
        $object = $this->generateFileName($fileName);
        // 配置信息
        $config = $this->config();
        // 调用获取云存储服务
        $cloudStorageFactory = CloudStorageFactory::make($config);
        $uploadId = $cloudStorageFactory->startChunk($object);

        return ['key' => $object, 'uploadId' => $uploadId];
    }

    /**
     * @Desc: 分段上传文件
     *
     * @Author: Keivn
     *
     * @Date: 2023/9/1 14:47
     */
    public function chunk(): array
    {
        try {
            $file = request()->file('file');
            if (! $file) {
                throw new \Exception(cloud_storage_trans('no_file'));
            }
            // 接取视频
            $ext = $file->getClientOriginalExtension();
            // 配置信息
            $config = $this->config();
            if (strpos($config->accept, $ext) === false) {
                throw new \Exception(sprintf(cloud_storage_trans('upload_accept_error'), $ext));
            }
            // 配置信息
            $config = $this->config();
            $uploadFile = $file->getRealPath();
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($config);
            // 上传分片。
            $object = request()->key;
            $uploadId = request()->uploadId;
            $partNumber = request()->partNumber;
            $partSize = request()->partSize;

            return $cloudStorageFactory->chunk($uploadFile, $object, $uploadId, $partNumber, $partSize);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @Desc: 完成上传文件
     *
     * @Author: Keivn
     *
     * @Date: 2023/9/1 14:47
     */
    public function finishChunk(): array
    {
        try {
            $partList = request()->partList;
            if (empty($partList)) {
                throw new \Exception(cloud_storage_trans('upload_chunk_data_not_exist'));
            }
            // 配置信息
            $config = $this->config();
            $object = request()->key;
            $uploadId = request()->uploadId;
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($config);
            // 上传分片。
            $size = $cloudStorageFactory->getSize($object, $uploadId);
            $data = $cloudStorageFactory->finishChunk($object, $uploadId, $partList);
            $this->store(array_merge($data, ['size' => $size]));

            // 插入数据库
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取加密链接
     *
     * @throws \Exception
     */
    public function signUrl(string $path): string
    {
        try {
            // 配置信息
            $config = $this->config();
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($config);

            // 取消上传。
            return $cloudStorageFactory->signUrl($path);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 生成目录文件名
     */
    public function generateFileName(string $fileName): string
    {
        //获取文件名
        $prefix = getAccept($fileName);

        //生成目录文件名
        return $prefix.'/'.date('Ymd').'/'.$fileName;
    }

    /**
     * 获取文件大小
     *
     * @throws \Exception
     */
    public function getSize(object $file): void
    {
        $cloudResourceService = new CloudResourceService;
        if ($cloudResourceService->getSize() * 1024 * 1024 <= $file->getSize()) {
            throw new \Exception(sprintf(cloud_storage_trans('upload_size_error'), $cloudResourceService->getSize()));
        }
    }
}
