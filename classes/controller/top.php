<?php

class Controller_Top extends Controller
{
    public function before()
    {
        parent::before();
        $this->redis = Redis::forge();

        // ログインチェック
        $authkey = Cookie::get('authkey');
        $this->uid = $authkey ? $this->redis->get(sprintf('auth:%s', $authkey)) : null;
        if ($this->uid) {
            $this->redis->expire(sprintf('auth:%s', $authkey), 3600);
        } else {
            Response::redirect('login');
        }
    }

    public function action_index()
    {
        $view = View::forge('top/index');

        $view->myuid = $this->uid;

        $form = Fieldset::forge();
        $form->add('message', 'メッセージ', array('type' => 'textarea', 'rows' => 5))
             ->add_rule('required')
             ->add_rule('max_length', 140);
        $form->add('submit', '', array('type' => 'submit', 'value' => '送信'));

        // form
        if ($form->validation()->run()) {
            $postid = $this->redis->incr('global:nextPostId');
            $message = preg_replace('/\n|\t/s', ' ', Input::post('message'));
            $post = sprintf("%s\t%d\t%s", $this->uid, time(), $message);
            $this->redis->set(sprintf('post:%d', $postid), $post);

            $this->redis->lpush(sprintf('uid:%d:posts', $this->uid), $postid);
            $followers = $this->redis->smembers(sprintf('uid:%d:followers', $this->uid));
            foreach ($followers as $fid) {
                $this->redis->lpush(sprintf('uid:%d:posts', $fid), $postid);
            }

            $this->redis->lpush('global:timeline', $postid);
            $this->redis->ltrim('global:timeline', 0, 1000);
        } else {
            $form->repopulate();
            $view->set_safe('errors', $form->validation()->show_errors());
        }

        $view->set_safe('html_form', $form->build(Uri::create('top')));

        // フォローしている
        $view->following = Mycommon::getFollowing($this->uid);

        // フォローされている
        $view->followers = Mycommon::getFollower($this->uid);

        // ツイート取得
        $view->messages = Mycommon::getMessageList($this->uid);

        // 全体のツイート取得
        $view->gmessages = Mycommon::getGlobalMessageList($this->uid);

        return $view;
    }

    public function action_follow($follow_uid = null)
    {
        if ($follow_uid && $this->redis->get(sprintf('uid:%d:username', $follow_uid))) {
            $this->redis->sadd(sprintf('uid:%d:following', $this->uid), $follow_uid);
            $this->redis->sadd(sprintf('uid:%d:followers', $follow_uid), $this->uid);
        }

        Response::redirect('top');
    }

    public function action_remove($remove_uid = null)
    {
        if ($remove_uid && $this->redis->get(sprintf('uid:%d:username', $remove_uid))) {
            $this->redis->srem(sprintf('uid:%d:following', $this->uid), $remove_uid);
            $this->redis->srem(sprintf('uid:%d:followers', $remove_uid), $this->uid);
        }

        Response::redirect('top');
    }
}