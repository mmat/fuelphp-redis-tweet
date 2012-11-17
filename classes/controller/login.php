<?php

class Controller_Login extends Controller
{
    public function before()
    {
        parent::before();
        $this->redis = Redis::forge();
    }

    public function action_index()
    {
        $view = View::forge('login/index');

        $form = Fieldset::forge();
        $form->add('username', 'ユーザー名', array('maxlength' => 20))
             ->add_rule('required');
        $form->add('password', 'パスワード', array('type' => 'password', 'maxlength' => 20))
             ->add_rule('required');
        $form->add('submit', '', array('type' => 'submit', 'value' => 'ログイン'));

        $form->repopulate();

        if (Input::post()) {
            if ($form->validation()->run()) {
                $input = $form->validation()->validated();

                $uid = $this->redis->get(sprintf('username:%s:uid', $input['username']));

                if ($uid && md5($input['password']) == $this->redis->get(sprintf('uid:%d:password', $uid))) {
                    $authkey = md5(uniqid(rand(), true));
                    $this->redis->setex(sprintf('auth:%s', $authkey), 3600, $uid);
                    Cookie::set('authkey', $authkey);
                    Response::redirect('top');
                } else {
                    $view->error_flg = true;
                }
            } else {
                $view->error_flg = true;
            }
        }

        $view->set_safe('html_form', $form->build(Uri::create('login')));

        return $view;
    }
}