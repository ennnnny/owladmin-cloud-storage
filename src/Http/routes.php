<?php

use Slowlyo\CloudStorage\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('cloud_storage', [Controllers\CloudStorageController::class, 'index']);

// 存储设置
Route::resource('cloud_storage/storage',Controllers\CloudStorageController::class);
// 资源管理
Route::resource('cloud_storage/resource',Controllers\CloudResourceController::class);
// 获取单纯列表数据
Route::get('get/cloud_storage/resource/getList', [Controllers\CloudResourceController::class, 'getList']);
// 下载
Route::get('cloud_storage/resource/download/{id}', [Controllers\CloudResourceController::class, 'download']);
// 简单上传
Route::post('cloud_storage/upload/receiver', [Controllers\CloudUploadController::class, 'receiver']);
// 切片上传
// 开始上传文件的准备
Route::post('cloud_storage/upload/startChunk', [Controllers\CloudUploadController::class, 'startChunk']);
// 分段上传文件
Route::post('cloud_storage/upload/chunk', [Controllers\CloudUploadController::class, 'chunk']);
// 完成分片上传
Route::post('cloud_storage/upload/finishChunk', [Controllers\CloudUploadController::class, 'finishChunk']);


