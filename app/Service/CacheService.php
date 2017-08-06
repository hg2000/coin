<?php
namespace App\Service;

use Illuminate\Support\Facades\Redis;

class CacheService {

    public function get($key) {

        if (config('database.redis.useRedis')) {
            return Redis::get($key);
        } else {
            return null;
        }
    }

    public function set($key, $value) {
        if (config('database.redis.useRedis')) {
            return Redis::set($key, $value);
        } else {
            return null;
        }
    }

    public function clear() {

        Redis::flushAll();
    }
}
