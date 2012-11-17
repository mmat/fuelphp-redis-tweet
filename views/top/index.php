<!DOCTYPE html>
<meta charset="utf-8">

<a href="<?php echo Uri::create('logout') ?>">ログアウト</a><br />

<?php if (isset($errors)): ?>
  <?php echo $errors ?>
<?php endif ?>

<?php echo $html_form ?>

<?php if ($messages): ?>
  <h2>タイムライン</h2>
  <hr />
  <?php foreach ($messages as $m): ?>
    <?php echo $m['username'] ?>&nbsp;
    <?php echo $m['time'] ?><br />
    <?php echo $m['message'] ?>
    <hr />
  <?php endforeach ?>
<?php endif ?>

<?php if ($followers): ?>
  <h2>フォローされている</h2>
  <ul>
  <?php foreach ($followers as $uid => $username): ?>
    <li><?php echo $username ?></li>
  <?php endforeach ?>
  </ul>
<?php endif ?>

<?php if ($following): ?>
  <h2>フォローしている</h2>
  <ul>
  <?php foreach ($following as $uid => $username): ?>
    <li>
      <?php echo $username ?>
      <?php if ($myuid != $uid): ?>
        <a href="<?php echo Uri::create('top/remove/'.$uid) ?>">[フォロー解除]</a>
      <?php endif ?>
    </li>
  <?php endforeach ?>
  </ul>
<?php endif ?>

<?php if ($gmessages): ?>
  <h2>全体のタイムライン</h2>
  <?php foreach ($gmessages as $m): ?>
    <?php echo $m['username'] ?>
    <?php if ($myuid != $m['uid'] && !Mycommon::checkFollowing($myuid, $m['uid'])): ?>
      <a href="<?php echo Uri::create('top/follow/'.$m['uid']) ?>">[フォロー]</a>
    <?php endif ?>
    <?php echo $m['time'] ?><br />
    <?php echo $m['message'] ?>
    <hr />
  <?php endforeach ?>
  </ul>
<?php endif ?>