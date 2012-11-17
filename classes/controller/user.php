<?php

class Controller_User extends Controller
{
    public function before()
    {
        parent::before();
        $this->redis = Redis::forge();
    }

    public function action_add()
    {
        $view = View::forge('user/add');

        $form = Fieldset::forge();
        $form->validation()->add_callable(new MyValidation());
        $form->add('username', 'ユーザー名', array('max_length' => 16))
             ->add_rule('required')
             ->add_rule('max_length', 16)
             ->add_rule('duplicate_user');
        $form->add('password', 'パスワード', array('type' => 'password', 'maxlength' => 16))
             ->add_rule('required')
             ->add_rule('min_length', 8)
             ->add_rule('max_length', 16);
        $form->add('submit', '', array('type' => 'submit', 'value' => '作成'));

        $form->repopulate();

        if ($form->validation()->run()) {

            $input = $form->validation()->validated();

            $uid = $this->redis->incr('global:nextUserId');
            $this->redis->set(sprintf('uid:%d:username', $uid), $input['username']);
            $this->redis->set(sprintf('uid:%d:password', $uid), md5($input['password']));
            $this->redis->set(sprintf('username:%s:uid', $input['username']), $uid);

            $authkey = md5(uniqid(rand(), true));
            $this->redis->setex(sprintf('auth:%s', $authkey), 3600, $uid);
            Cookie::set('authkey', $authkey);

            Response::redirect('user/add_comp');

        } else {
            $view->set_safe('errors', $form->validation()->show_errors());
        }

        $view->set_safe('html_form', $form->build(Uri::create('user/add')));

        return $view;
    }

    public function action_add_comp()
    {
        $authkey = Cookie::get('authkey');
        $uid = $authkey ? $this->redis->get(sprintf('auth:%s', $authkey)) : null;
        if (!$uid) {
            Response::redirect('user/add');
        }

        return View::forge('user/add_comp');
    }
}