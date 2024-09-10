<?php defined('C5_EXECUTE') or die('Access Denied'); ?>
<!DOCTYPE html>
<html lang="<?= Localization::activeLanguage(); ?>">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
      View::element('header_required', [
          'pageTitle' => isset($pageTitle) ? $pageTitle : '',
          'pageDescription' => isset($pageDescription) ? $pageDescription : '',
          'pageMetaKeywords' => isset($pageMetaKeywords) ? $pageMetaKeywords : ''
      ]);
    ?>
    <link rel="stylesheet" type="text/css" href="/concrete/themes/concrete/main.css">
  </head>
  <body class='<?= $c->getPageWrapperClass()?>'>
    <div class="container mt-5 text-center">
        <?php 
        View::element('system_errors', [
            'format' => 'block',
            'error' => isset($error) ? $error : null,
            'success' => isset($success) ? $success : null,
            'message' => isset($message) ? $message : null,
        ]);  

        ?>
        <h1 class="error"><?=t('Page Not Found')?></h1>

        <?=t('No page could be found at this address.')?>
        <br>
        <br>

        <a href="<?=DIR_REL?>/"><?=t('Back to Home')?></a>.
    </div>
    <?php
      View::element('footer_required');
    ?>
  </body>
</html>