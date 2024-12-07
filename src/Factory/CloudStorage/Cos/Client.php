<?php

namespace Ennnnny\CloudStorage\Factory\CloudStorage\Cos;

use Qcloud\Cos\Client as COSClient;

class Client
{
    protected COSClient $cosClient;

    protected array $config;

    public function __construct(array $data)
    {
        $this->config = $data;
        $config = [
            'region' => $this->config['region'],
            'schema' => request()->server('REQUEST_SCHEME'),
            'credentials' => [
                'secretId' => $this->config['secret_id'],
                'secretKey' => $this->config['secret_key'],
            ],
            'domain' => $this->config['domain'] ?? null,
        ];
        $this->cosClient = new COSClient($config);
    }

    /**
     * 简单上传
     *
     * @throws \Exception
     */
    public function receiver(string $object, string $filePath): array
    {
        try {
            $this->cosClient->putObject([
                'Bucket' => $this->config['bucket'], //格式：BucketName-APPID
                'Key' => $object,
                'Body' => fopen($filePath, 'rb'),
            ]);
            // 请求成功
            $path = $this->signUrl($object);

            return ['value' => $path, 'path' => $object];
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤1：初始化一个分片上传事件，并获取uploadId。
     *
     * @throws \Exception
     */
    public function startChunk(string $object): ?string
    {
        try {
            $result = $this->cosClient->createMultipartUpload([
                'Bucket' => $this->config['bucket'], // 存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                'Key' => $object,
            ]);

            // 请求成功
            return $result['UploadId'];
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤2：上传分片。
     *
     * @throws \Exception
     */
    public function chunk(string $uploadFile, string $object, string $uploadId, int $partNumber = 1, int $partSize = 0): array
    {
        try {
            $result = $this->cosClient->uploadPart([
                'Bucket' => $this->config['bucket'], //格式：BucketName-APPID
                'Key' => $object,
                'Body' => fopen($uploadFile, 'rb'),
                'UploadId' => $uploadId, //UploadId 为对象分块上传的 ID，在分块上传初始化的返回参数里获得
                'PartNumber' => $partNumber, //PartNumber 为分块的序列号，COS 会根据携带序列号合并分块
            ]);

            // 请求成功
            return ['eTag' => trim($result['ETag'], '"')];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤3：完成上传。
     *
     * @throws \Exception
     */
    public function finishChunk(string $object, string $uploadId, array $partList): array
    {
        try {
            foreach ($partList as &$item) {
                $item['ETag'] = $item['eTag'];
                unset($item['eTag']);
                $item['PartNumber'] = $item['partNumber'];
                unset($item['partNumber']);
            }
            $this->cosClient->completeMultipartUpload([
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

    /**
     * 列举已上传的分片
     *
     * @throws \Exception
     */
    public function listParts(string $object, string $uploadId): object
    {
        try {
            // 请求成功
            return $this->cosClient->listParts([
                'Bucket' => $this->config['bucket'], //格式：BucketName-APPID
                'Key' => $object,
                'UploadId' => $uploadId,
            ]);
        } catch (\Exception $e) {
            // 请求失败
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取文件大小
     *
     * @throws \Exception
     */
    public function getSize(string $object, string $uploadId): int
    {
        $partsInfo = $this->listParts($object, $uploadId);
        $size = 0;
        if (! empty($partsInfo)) {
            foreach ($partsInfo['Parts'] as $partInfo) {
                $size += $partInfo['Size'];
            }
        }

        return $size;
    }

    /**
     * 生成上传加密链接
     *
     * @param  string  $accessMode
     *                              getObjectUrl 使用封装的 getObjectUrl 获取下载签名生成临时密钥预签名
     *
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function signUrl(string $object, string $accessMode = 'inline'): string
    {
        try {
            // 生成签名URL。
            return $this->cosClient->getObjectUrl($this->config['bucket'], $object, '+'.env('CLOUD_STORAGE_TIMEOUT', 3600).' minutes');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
