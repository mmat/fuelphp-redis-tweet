<!DOCTYPE html>
<meta charset="utf-8">

<h1>SIGN UP</h1>

<?php if (isset($errors)): ?>
<?php echo $errors ?>
<?php endif ?>

<?php echo $html_form ?>

<a href="<?php echo Uri::create('login/index') ?>">ログインはこちら</a>