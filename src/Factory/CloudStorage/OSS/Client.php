<?php

namespace Slowlyo\CloudStorage\Factory\CloudStorage\OSS;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\Credentials\Credentials;
use OSS\Http\RequestCore_Exception;
use OSS\OssClient;

class Client
{
    protected OssClient $ossClient;

    protected array $config;

    /**
     * @throws OssException
     */
    public function __construct(array $data)
    {
        $this->config = $data;
        $provider = new Config($this->config);
        $config  = array(
            "provider" => $provider,
            "endpoint" => $this->config['endpoint'],
        );
        $this->ossClient = new OssClient($config);
    }

    /**
     * @throws OssException
     */
    public function getCredentials(): Credentials
    {
        // TODO: Implement getCredentials() method.
        return new Credentials($this->config['access_key'], $this->config['secret_key']);
    }

    /**
     * 简单上传
     * @param string $object
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    public function receiver(string $object,string $filePath):array
    {
        try{
            $this->ossClient->uploadFile($this->config['bucket'], $object, $filePath);
            $path = $this->signUrl($object);
            return array('value' => $path,'path' => $object);
        } catch(OssException $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 步骤1：初始化一个分片上传事件，并获取uploadId。
     * @param string $object
     * @return string|null
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function startChunk(string $object): ?string
    {
        try{
            $initOptions = array(
                OssClient::OSS_HEADERS  => array(),
                // 指定该Object被下载时的网页缓存行为。
                'Cache-Control' => 'no-cache',
                // 指定该Object被下载时的名称。
                'Content-Disposition' => 'attachment;filename='.$object,
                // 指定该Object被下载时的内容编码格式。
                'Content-Encoding' => 'utf-8',
                // 指定过期时间，单位为毫秒。
                'Expires' => 150,
                // 指定初始化分片上传时是否覆盖同名Object。此处设置为true，表示禁止覆盖同名Object。
                'x-oss-forbid-overwrite' => 'true',
                // 指定上传该Object的每个part时使用的服务器端加密方式。
                'x-oss-server-side-encryption'=> 'KMS',
                // 指定Object的加密算法。
                'x-oss-server-side-data-encryption'=>'SM4',
                // 指定Object的存储类型。
                'x-oss-storage-class' => 'Standard',
            );
            //返回uploadId。uploadId是分片上传事件的唯一标识，您可以根据uploadId发起相关的操作，如取消分片上传、查询分片上传等。
            return $this->ossClient->initiateMultipartUpload($this->config['bucket'], $object, $initOptions);
        } catch(OssException $e) {
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
        try {
            $upOptions = array(
                // 上传文件。
                $this->ossClient::OSS_FILE_UPLOAD => $uploadFile,
                // 设置分片号。
                $this->ossClient::OSS_PART_NUM => $partNumber,
                // 是否开启MD5校验，true为开启。
                $this->ossClient::OSS_CHECK_MD5 => true,
            );
            try {
                // 上传分片。
                $uploadPart = $this->ossClient->uploadPart($this->config['bucket'], $object, $uploadId, $upOptions);
                return array('eTag'=>trim($uploadPart,'"'));
            } catch(OssException $e) {
                throw new \Exception($e->getMessage());
            }
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
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function finishChunk(string $object,string $uploadId,array $partList): array
    {
        try{
            // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            // 阿里云需要partList格式为：[['PartNumber'=>'','ETag'=>'']]
            $comOptions['headers'] = array(
//                // 指定完成分片上传时是否覆盖同名Object。此处设置为true，表示禁止覆盖同名Object。
//                 'x-oss-forbid-overwrite' => 'true',
//                // 如果指定了x-oss-complete-all:yes，则OSS会列举当前uploadId已上传的所有Part，然后按照PartNumber的序号排序并执行CompleteMultipartUpload操作。
//                 'x-oss-complete-all'=> 'yes'
            );
            foreach ($partList as &$item) {
                $item['ETag'] = $item['eTag'];
                unset($item['eTag']);
                $item['PartNumber'] = $item['partNumber'];
                unset($item['partNumber']);
            }
            $this->ossClient->completeMultipartUpload($this->config['bucket'], $object, $uploadId, $partList, $comOptions);
            $path = $this->signUrl($object);
            return array('value' => $path,'path' => $object);
        }catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 列举已上传的分片
     * @param string $object
     * @param string $uploadId
     * @return \OSS\Model\ListPartsInfo
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function listParts(string $object, string $uploadId): \OSS\Model\ListPartsInfo
    {
        try {
            return $this->ossClient->listParts($this->config['bucket'], $object, $uploadId);
        }catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * 获取文件大小
     * @throws RequestCore_Exception
     */
    public function getSize(string $object, string $uploadId): int
    {
        $partsInfo = $this->listParts($object,$uploadId);
        $size = 0;
        if(!empty($partsInfo)) {
            foreach ($partsInfo->getListPart() as $partInfo) {
                $size += $partInfo->getSize();
            }
        }
        return $size;
    }


    /**
     * 生成加密链接
     * @param string $object
     * @param string $accessMode
     * @return string
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function signUrl(string $object ,string $accessMode = 'inline'):string
    {
        try{
            $options = array(
                // 填写Object的versionId。
                "response-content-disposition"=> $accessMode,
            );
            // 生成签名URL。
            return $this->ossClient->signUrl($this->config['bucket'], $object, env('CLOUD_STORAGE_TIMEOUT',3600), "GET", $options);
        }catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }


}
