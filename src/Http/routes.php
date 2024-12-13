<?php

use Illuminate\Support\Facades\Route;

Route::get('cloud_storage', [\Ennnnny\CloudStorage\Http\Controllers\CloudStorageController::class, 'index']);

// 存储设置
Route::resource('cloud_storage/storage', \Ennnnny\CloudStorage\Http\Controllers\CloudStorageController::class);
// 资源管理
Route::resource('cloud_storage/resource', \Ennnnny\CloudStorage\Http\Controllers\CloudResourceController::class);
// 获取单纯列表数据
Route::get('get/cloud_storage/resource/getList', [\Ennnnny\CloudStorage\Http\Controllers\CloudResourceController::class, 'getList']);
// 下载
Route::get('cloud_storage/resource/download/{id}', [\Ennnnny\CloudStorage\Http\Controllers\CloudResourceController::class, 'download']);
// 简单上传
Route::post('cloud_storage/upload/receiver/{id}', [\Ennnnny\CloudStorage\Http\Controllers\CloudUploadController::class, 'receiver']);
// 切片上传
// 开始上传文件的准备
Route::post('cloud_storage/upload/startChunk/{id}', [\Ennnnny\CloudStorage\Http\Controllers\CloudUploadController::class, 'startChunk']);
// 分段上传文件
Route::post('cloud_storage/upload/chunk/{id}', [\Ennnnny\CloudStorage\Http\Controllers\CloudUploadController::class, 'chunk']);
// 完成分片上传
Route::post('cloud_storage/upload/finishChunk/{id}', [\Ennnnny\CloudStorage\Http\Controllers\CloudUploadController::class, 'finishChunk']);
