<?php

namespace Ennnnny\CloudStorage\Traits;

use Ennnnny\CloudStorage\Factory\CloudStorage\CloudStorageFactory;
use Ennnnny\CloudStorage\Models\CloudResource;
use Ennnnny\CloudStorage\Models\CloudStorage;
use Ennnnny\CloudStorage\Services\CloudUploadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait UploadTrait
{
    /**
     * 获取文件下载链接
     *
     * @throws \Exception
     */
    public function cloudGetUrl($resource_id, $openDomain = '')
    {
        $info = CloudResource::query()->find($resource_id, ['id', 'url']);
        if ($info) {
            if (!empty($openDomain)) {
                return $openDomain . $info->url;
            }
            try {
                $res = $info->getCloudStoragePath($info->url, $info->id);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $res = null;
            }

            return $res;
        }

        return null;
    }

    /**
     * 简单上传
     *
     * @throws \Exception
     */
    public function cloudSimpleUpload($file_path, $storage_id, $fileName = '', $cover = false)
    {
        //file exist？
        if (!file_exists($file_path)) {
            return null;
        }
        $storage = CloudStorage::query()->where('enabled', 1)->find($storage_id);
        if ($storage) {
            $service = new CloudUploadService;
            $size = filesize($file_path);
            $config = $storage->config;
            if (empty($fileName)) {
                $file_res = getFilenameAndExtension($file_path);
                $fileName = $file_res['filename'];
                if (!empty($file_res['extension'])) {
                    $fileName .= '.' . $file_res['extension'];
                }
            }
            $object = $service->generateFileName($fileName, $config);
            $exits = CloudResource::query()->where('url', $object)->first();
            if ($exits && !$cover) {
                return $exits;
            }
            // 调用获取云存储服务
            $cloudStorageFactory = CloudStorageFactory::make($storage);
            $data = $cloudStorageFactory->receiver($object, $file_path);
            if ($exits && $cover) {
                return $exits;
            }
            // 插入数据库
            $service->store(array_merge($data, ['size' => $size]), $storage_id);

            return CloudResource::query()->where('url', $object)->first();
        }

        return null;
    }

    /**
     * 分片上传
     *
     * @throws \Exception
     */
    public function cloudChunkUpload($file_path, $storage_id, $fileName = '', $chunk_size = 50 * 1024 * 1024, $min_size = 5 * 1024 * 1024 * 1024, $cover = false)
    {
        //file exist？
        if (!file_exists($file_path)) {
            return null;
        }
        $storage = CloudStorage::query()->where('enabled', 1)->find($storage_id);
        if ($storage) {
            $size = filesize($file_path);
            if ($size < $min_size) {
                return $this->cloudSimpleUpload($file_path, $storage_id, $fileName, $cover);
            } else {
                $service = new CloudUploadService;
                $config = $storage->config;
                if (empty($fileName)) {
                    $file_res = getFilenameAndExtension($file_path);
                    $fileName = $file_res['filename'];
                    if (!empty($file_res['extension'])) {
                        $fileName .= '.' . $file_res['extension'];
                    }
                }
                $object = $service->generateFileName($fileName, $config);
                $exits = CloudResource::query()->where('url', $object)->first();
                if ($exits && !$cover) {
                    return $exits;
                }
                // 调用获取云存储服务
                $cloudStorageFactory = CloudStorageFactory::make($storage);
                $uploadId = $cloudStorageFactory->startChunk($object);

                //文件根据$chunk_size分割
                $file = fopen($file_path, 'r');
                $partNumber = 1;
                $partList = [];
                while (!feof($file)) {
                    $part = fread($file, $chunk_size);
                    if ($part === false) {
                        break;
                    }
                    //$part临时储存
                    if (Storage::disk('local')->put($uploadId . '_' . $partNumber, $part)) {
                        $file_path_temp = Storage::disk('local')->path($uploadId . '_' . $partNumber);
                        $data = $cloudStorageFactory->chunk($file_path_temp, $object, $uploadId, $partNumber, $chunk_size);
                        $partList[] = [
                            'partNumber' => $partNumber,
                            'eTag' => $data['eTag'],
                        ];
                        Storage::disk('local')->delete($uploadId . '_' . $partNumber);
                        $partNumber++;
                    } else {
                        $partList = [];
                        break;
                    }
                }
                if (count($partList) > 0) {
                    $res = $cloudStorageFactory->finishChunk($object, $uploadId, $partList);
                    if ($exits && $cover) {
                        return $exits;
                    }
                    $service->store(array_merge($res, ['size' => $size]), $storage_id);

                    return CloudResource::query()->where('url', $object)->first();
                } elseif ($exits) {
                    return $exits;
                }
            }
        }

        return null;
    }
}
