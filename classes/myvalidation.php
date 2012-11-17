<?php

class MyValidation
{
    public static function _validation_duplicate_user($username)
    {
        $redis = \Redis::instance();

        if ($redis->get(sprintf('username:%s:*', $username))) return false;

        return true;
    }
}