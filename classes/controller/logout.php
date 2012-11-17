<?php

class Controller_Logout extends Controller
{
    public function before()
    {
        parent::before();
        $this->redis = Redis::forge();
    }

    public function action_index()
    {
        $authkey = Cookie::get('authkey');

        if ($authkey) $this->redis->del(sprintf('auth:%s', $authkey));

        Response::redirect('login');
    }
}