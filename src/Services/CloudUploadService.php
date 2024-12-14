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
    public function config($id = null): object
    {
        $cloudStorage = new CloudStorage;
        if (! empty($id)) {
            $data = $cloudStorage->find($id);
        } else {
            $data = $cloudStorage->getCache();
        }
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
    public function store(array $data, $id = null): void
    {
        if (! empty($data)) {
            $file_res = getFilenameAndExtension($data['path']);
            $ext = $file_res['extension'];
            $title = $file_res['filename'];
            //需要转换
            $type = isset($data['path']) ? getAccept($data['path']) : 'other';
            $cloudResourceService = new CloudResourceService;
            $cloudResourceService->store([
                'title' => $title,
                'size' => $data['size'] ?? 0,
                'url' => $data['path'],
                'is_type' => array_flip($cloudResourceService::fileType)[$type],
                'storage_id' => empty($id) ? $cloudResourceService->getStorageId() : $id,
                'extension' => $ext,
            ]);
        }
    }

    /**
     * 简单上传
     *
     * @throws \Exception
     */
    public function receiver($id): mixed
    {
        try {
            $file = request()->file('file');
            if (! $file) {
                throw new \Exception(cloud_storage_trans('no_file'));
            }
            //文件限制大小  更换最后的数字调整文件上传的大小
            $this->getSize($file, $id);
            $fileName = $file->getClientOriginalName();
            $size = $file->getSize();
            // 配置信息
            $config = $this->config($id);
            $object = $this->generateFileName($fileName, $config->config);
            // 判断是否已存在
            $is_exits = CloudResourceService::make()->query()->where('url', $object)->exists();
            if ($is_exits) {
                throw new \Exception('文件已存在');
            }
            $filePath = $file->getRealPath();
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($config);
            $data = $cloudStorageFactory->receiver($object, $filePath);
            // 插入数据库
            $this->store(array_merge($data, ['size' => $size]), $id);

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 开始上传文件的准备
     *
     * @param  null  $id
     *
     * @throws \Exception
     */
    public function startChunk($id = null): array
    {
        //第一步 ： 初始化一个分片上传事件，获取uploadId。
        $fileName = request()->name;
        // 配置信息
        $config = $this->config($id);
        $object = $this->generateFileName($fileName, $config->config);
        // 判断是否已存在
        $is_exits = CloudResourceService::make()->query()->where('url', $object)->exists();
        if ($is_exits) {
            throw new \Exception('文件已存在');
        }
        // 调用获取云存储服务
        $cloudStorageFactory = CloudStorageFactory::make($config);
        $uploadId = $cloudStorageFactory->startChunk($object);

        return ['key' => $object, 'uploadId' => $uploadId];
    }

    /**
     * 分段上传文件
     */
    public function chunk($id = null): array
    {
        try {
            $file = request()->file('file');
            if (! $file) {
                throw new \Exception(cloud_storage_trans('no_file'));
            }
            // 配置信息
            $config = $this->config($id);

            $ext = $file->getClientOriginalExtension();
            if (! empty($config->accept) && ! str_contains($config->accept, $ext)) {
                throw new \Exception(sprintf(cloud_storage_trans('upload_accept_error'), $ext));
            }

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
     * 完成上传文件
     */
    public function finishChunk($id = null): array
    {
        try {
            $partList = request()->partList;
            if (empty($partList)) {
                throw new \Exception(cloud_storage_trans('upload_chunk_data_not_exist'));
            }
            // 配置信息
            $config = $this->config($id);
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
    public function signUrl(string $path, $id = null): string
    {
        try {
            // 配置信息
            $config = $this->config($id);
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
    public function generateFileName(string $fileName, $config = []): string
    {
        if (! empty($config['root'])) {
            return $config['root'].'/'.$fileName;
        }
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
    public function getSize(object $file, $id = null): void
    {
        $cloudResourceService = new CloudResourceService;
        $limit_size = $cloudResourceService->getSize($id);
        if ($limit_size != 0 && $limit_size * 1024 * 1024 <= $file->getSize()) {
            throw new \Exception(sprintf(cloud_storage_trans('upload_size_error'), $limit_size));
        }
    }
}
