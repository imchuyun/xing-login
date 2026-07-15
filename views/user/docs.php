<?php
$pageTitle = '接入文档';
$isPublic = false;
ob_start();
?>

<?php include ML_ROOT . '/views/shared/docs_content.php'; ?>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/user.php'; ?>
