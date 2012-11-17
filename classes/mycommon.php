<?php

class Mycommon
{
    public static function getFollowing($uid)
    {
        $redis = Redis::instance();

        $following = array();
        $members = $redis->smembers(sprintf('uid:%d:following', $uid));
        if ($members) {
            foreach ($members as $member_uid) {
                $following[$member_uid] = $redis->get(sprintf('uid:%d:username', $member_uid));
            }
        }

        return $following;
    }

    public static function getFollower($uid)
    {
        $redis = Redis::instance();

        $followers = array();
        $members = $redis->smembers(sprintf('uid:%d:followers', $uid));
        if ($members) {
            foreach ($members as $member_uid) {
                $followers[$member_uid] = $redis->get(sprintf('uid:%d:username', $member_uid));
            }
        }

        return $followers;
    }

    public static function getMessageList($uid)
    {
        $redis = Redis::instance();

        $messages = array();
        $timeline = $redis->lrange(sprintf('uid:%d:posts', $uid), 0, 100);
        if ($timeline) {
            foreach ($timeline as $postid) {
                $messages[] = self::getPost($postid);
            }
        }

        return $messages;
    }

    public static function getGlobalMessageList($uid)
    {
        $redis = Redis::instance();

        $messages = array();
        $timeline = $redis->lrange('global:timeline', 0, 100);
        if ($timeline) {
            foreach ($timeline as $postid) {
                $messages[] = self::getPost($postid);
            }
        }

        return $messages;
    }

    public static function getPost($postid)
    {
        $redis = Redis::instance();

        list($uid, $unixtime, $message) = explode("\t", $redis->get(sprintf('post:%d', $postid)));
        $username = $redis->get(sprintf('uid:%d:username', $uid));
        $post_info = array(
            'uid'           => $uid,
            'username'      => $redis->get(sprintf('uid:%d:username', $uid)),
            'time'          => date('Y-m-d H:i:s', $unixtime),
            'message'       => $message
        );

        return $post_info;
    }

    public static function checkFollowing($myuid, $target_uid)
    {
        $redis = Redis::instance();

        if ($redis->sismember(sprintf('uid:%d:following', $myuid), $target_uid)) {
            return true;
        }

        return false;
    }
}