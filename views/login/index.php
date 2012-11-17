<!DOCTYPE html>
<meta charset="utf-8">

<h1>LOGIN</h1>

<?php if (isset($error_flg)): ?>
ログインに失敗しました<br />
<?php endif ?>

<?php echo $html_form ?>

<a href="<?php echo Uri::create('user/add') ?>">ユーザー登録はこちら</a>