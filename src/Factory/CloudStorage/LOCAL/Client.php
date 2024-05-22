<?php

namespace Slowlyo\CloudStorage\Factory\CloudStorage\LOCAL;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Client
{
    // 分片存储目录
    private string $chunkDir;
    // 上传的唯一标识符
    private string $uploadId;
    // 配置信息
    protected array $config;

    protected object $disk;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->config = $data;
        $this->chunkDir = '/local/';
        $this->disk = Storage::disk('public');
    }

    /**
     * 简单上传
     * @param string $object
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    public function receiver(string $object,string $filePath): array
    {
        try {
            $this->disk->put($this->chunkDir.$object, file_get_contents($filePath));
            // 请求成功
            $path = $this->signUrl($object);
            return array('value' => $path,'path' => $object);
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤1：初始化一个分片上传事件，并获取uploadId。
     * @param string $object
     * @return string|null
     * @throws \Exception
     */
    public function startChunk(string $object): ?string
    {
        try{
            // 生成一个唯一的上传标识符
            $this->uploadId = Str::uuid();
            // 请求成功
            return $this->uploadId;
        }catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤2：上传分片。
     * @param string $uploadFile
     * @param string $object
     * @param string $uploadId
     * @param int $partNumber
     * @param int $partSize
     * @return array
     * @throws \Exception
     */
    public function chunk(string $uploadFile,string $object,string $uploadId,int $partNumber = 1,int $partSize = 0): array
    {
        try{
            // 验证上传ID和分片索引
            // 检查是否已有该上传标识的信息  //$partNumber
            if (empty($uploadId) || !is_numeric($partNumber) || $partNumber <= 0) {
                throw new \Exception(cloud_storage_trans('"incorrect_upload_id_or_shard_index"'));// 错误的上传ID或分片索引
            }
            // 从请求中获取上传标识、分片索引和分片文件  // $uploadId
            // 例如，保存到 storage/app/uploads/{$uploadId}/{$chunkIndex}
            $chunkFile = $this->chunkDir."chunk/{$uploadId}/{$partNumber}";
            // 保存分片文件（可以使用 Laravel 的文件系统 API）
            $eTag = uniqid();
            // 构建分片文件名
            $this->disk->put($chunkFile, file_get_contents($uploadFile));
            $chunkList = [
                'partNumber' => $partNumber,
                'eTag' => $eTag,
                'path' => $chunkFile,
                'key'  => $object,
                'size' => $partSize,
            ];
            app('redis')->lPush($uploadId,json_encode($chunkList));
            app('redis')->expire($uploadId, 24 * 3600);
            // 请求成功
            return array('eTag'=>$eTag);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤3：完成上传。
     * @param string $object
     * @param string $uploadId
     * @param array $partList
     * @return array
     * @throws \Exception
     */
    public function finishChunk(string $object,string $uploadId,array $partList): array
    {
        try{
            // 验证上传ID
            if (empty($uploadId)) {
                throw new \Exception(cloud_storage_trans('incorrect_upload_id')); // 错误的上传ID
            }
            // 合并分片文件
            // 例如，使用临时文件或流来合并所有分片
            $filePath = storage_path('app/public').$this->chunkDir;
            $file = $filePath.$object;
            if (!file_exists($file)) {
                $this->disk->put($this->chunkDir.$object,'');
                $handle = fopen($file, 'wb');
                foreach ($partList as $index => $part) {
                    $chunkPath = $this->chunkDir."chunk/{$uploadId}/{$part['partNumber']}";
                    $chunkContent = $this->disk->get($chunkPath);
                    fwrite($handle, $chunkContent);
                    // 可选：删除已合并的分片以节省空间
                    $this->disk->delete($chunkPath);
                }
                fclose($handle);
            }
            // 返回是否成功创建最终文件
            $path = $this->signUrl($object);
            // 请求成功
            return array('value' => $path,'path' => $object);
        }catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取分片列表
     * @param string $object
     * @param string $uploadId
     * @return array
     * @throws \Exception
     */
    public function listParts(string $object, string $uploadId): array
    {
        try{
            $data = app('redis')->lRange($uploadId,0,-1);
            if(empty($data)) {
                throw new \Exception(cloud_storage_trans('fragment_not_found'));
            }
            return $data;
        }catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取文件大小
     * @throws \Exception
     */
    public function getSize(string $object, string $uploadId): int
    {
        $partsInfo = $this->listParts($object,$uploadId);
        $size = 0;
        if(!empty($partsInfo)) {
            foreach ($partsInfo as $index => $partInfo) {
                $json = json_decode($partInfo,true);
                $size += $json["size"];
            }
        }
        return $size;
    }

    /**
     * 生成上传加密链接
     * @param string $object
     * @param string $accessMode
     * getObjectUrl 使用封装的 getObjectUrl 获取下载签名生成临时密钥预签名
     * @return string
     * @throws \Exception
     */
    public function signUrl(string $object ,string $accessMode = 'inline'):string
    {
        try{
            return $this->config['domain'] ? $this->config['domain'].'/storage/local/'.$object : asset('storage/local/'.$object);
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}
