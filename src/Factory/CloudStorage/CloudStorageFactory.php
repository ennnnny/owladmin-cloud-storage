<?php

namespace Ennnnny\CloudStorage\Factory\CloudStorage;

class CloudStorageFactory implements BaseFactory
{
    public static function make(object $config): object
    {
        $className = __NAMESPACE__.'\\'.ucfirst(strtolower(($config->driver))).'\\'.'Client';
        if (! class_exists($className)) {
            throw new \Exception('类不存在');
        }

        return new $className($config->config);
    }
}
