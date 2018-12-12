<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of Redis
 *
 * @author Administrator
 */
class Redis extends \Think\Cache\Driver\Redis {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取hash表值
     * @param type $key
     * @param type $hashKeys
     * @return type
     */
    public function hget($key, $hashKeys) {
        N('cache_read', 1);
        $key = (is_object($key) || is_array($key)) ? json_encode($key) : $key;
        $hashKeys = (is_object($hashKeys) || is_array($hashKeys)) ? json_encode($hashKeys) : $hashKeys;
        $result = $this->handler->hGet($this->options['prefix'] . $key, $hashKeys);
        return $result;
    }

    /**
     * 获取存储在哈希表中所有字段的值
     * @param type $key 
     * @return type
     */
    public function hgetall($key) {
        N('cache_read', 1);
        $key = (is_object($key) || is_array($key)) ? json_encode($key) : $key;
        $result = $this->handler->hGetAll($this->options['prefix'] . $key);
        return $result;
    }

    /* 同时将多个 field-value (域-值)对设置到哈希表 key 中。
     * @param   string  $key
     * @param   array   $hashKeys key → value array
     * @return  bool
     */
    public function hmset($key, $hashKeys) {
        N('cache_read', 1);
        $key = (is_object($key) || is_array($key)) ? json_encode($key) : $key;
        $result = $this->handler->hMset($key, $hashKeys);
        return $result;
    }

    /* 将哈希表 key 中的字段 field 的值设为 value 。
     * @param   string  $key
     * @param   string  $hashKey
     * @param   string  $value
     * @return  bool    TRUE if the field was set, FALSE if it was already present.
     */
    public function hset($key, $hashKey, $value) {
        N('cache_read', 1);
        $key = (is_object($key) || is_array($key)) ? json_encode($key) : $key;
        $hashKey = (is_object($hashKey) || is_array($hashKey)) ? json_encode($hashKey) : $hashKey;
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $this->handler->hSet($key, $hashKey, $value);
        return $result;
    }

    /**
     * publish
     * @param string $value
     * @return int 队列长度
     */
    public function publish($key,$value)
    {
        N('cache_read', 1);
        $key = (is_object($key) || is_array($key)) ? json_encode($key) : $key;
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $result = $this->handler->publish($key, $value);
        return $result;
    }
}
