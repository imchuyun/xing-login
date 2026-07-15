<?php
$pageTitle = '接入文档';
$isPublic = true;
ob_start();
?>

<div class="container" style="padding: 2rem 1rem; max-width: 1100px; margin: 0 auto;">
<?php include ML_ROOT . '/views/shared/docs_content.php'; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php include ML_ROOT . '/views/layouts/main.php'; ?>
