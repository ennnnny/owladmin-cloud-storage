<?php

namespace Ennnnny\CloudStorage\Factory\CloudStorage\Kodo;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\UploadManager;

class Client
{
    protected Auth $kodoClient;

    protected array $config;

    private string $uploadId;

    public function __construct(array $data)
    {
        $this->config = $data;
        $this->kodoClient = new Auth($this->config['access_key'], $this->config['secret_key']);
    }

    /**
     * 简单上传
     *
     * @throws \Exception
     */
    public function receiver(string $object, string $filePath): array
    {
        try {
            // 生成上传Token
            $token = $this->kodoClient->uploadToken($this->config['bucket']);
            // 构建 UploadManager 对象
            $uploadMgr = new UploadManager;
            [$ret, $err] = $uploadMgr->putFile($token, $object, $filePath, null, 'application/octet-stream', true, null, 'v2');
            // 请求成功
            $path = $this->signUrl($object);

            return ['value' => $path, 'path' => $object];
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    public function startChunk(string $object): ?string
    {
        try {
            $token = $this->kodoClient->uploadToken($this->config['bucket']);
            $uploadMgr = new UploadManager;
            [$ret, $err] = $uploadMgr->putFile($token, $object, null,
                null, 'application/octet-stream', false,
                null, 'v2', $partSize);

            // 请求成功
            return $this->uploadId;
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    public function chunk(string $uploadFile, string $object, string $uploadId, int $partNumber = 1, int $partSize = 0): array
    {
        try {
            $token = $this->kodoClient->uploadToken($this->config['bucket']);
            $uploadMgr = new UploadManager;
            //写入一个数据
            $eTag = uniqid();
            [
                'UploadId' => $this->uploadId,
                'etags' => $eTag,
                'expiredAt' => time() + 60 * 3600,

            ];
            [$ret, $err] = $uploadMgr->putFile($token, $object, $uploadFile,
                null, 'application/octet-stream', false,
                null, 'v2', $partSize);
            dd($ret, $err);

            //            $result = $this->cosClient->uploadPart(array(
            //                'Bucket' => $this->config['bucket'], //格式：BucketName-APPID
            //                'Key' => $object,
            //                'Body' => $uploadFile,
            //                'UploadId' => $uploadId, //UploadId 为对象分块上传的 ID，在分块上传初始化的返回参数里获得
            //                'PartNumber' => $partNumber, //PartNumber 为分块的序列号，COS 会根据携带序列号合并分块
            //            ));
            // 请求成功
            return ['eTag' => trim($result['ETag'], '"')];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function finishChunk(string $object, string $uploadId, array $partList): array
    {
        try {
            foreach ($partList as &$item) {
                $item['ETag'] = $item['eTag'];
                unset($item['eTag']);
                $item['PartNumber'] = $item['partNumber'];
                unset($item['partNumber']);
            }
            $result = $this->cosClient->completeMultipartUpload([
                'Bucket' => $this->config['bucket'], // 存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                'Key' => $object,
                'UploadId' => $uploadId,
                'Parts' => $partList,
            ]);
            $path = $this->signUrl($object);

            // 请求成功
            return ['value' => $path, 'path' => $object];
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    public function listParts(string $object, string $uploadId)
    {
        try {
            $result = $this->cosClient->listParts([
                'Bucket' => $this->config['bucket'], //格式：BucketName-APPID
                'Key' => $object,
                'UploadId' => $uploadId,
            ]);
            // 请求成功
            print_r($result);
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 生成上传加密链接
     *
     * @param  string  $accessMode
     *                              getObjectUrl 使用封装的 getObjectUrl 获取下载签名生成临时密钥预签名
     *
     * @throws \Exception
     */
    public function signUrl(string $object, string $accessMode = 'inline'): string
    {
        try {
            // 生成签名URL。
            return $this->kodoClient->privateDownloadUrl($this->config['domain'].'/'.$object);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
