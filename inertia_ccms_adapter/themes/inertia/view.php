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
  </head>
  <body>
    <?php 
      View::element('system_errors', [
          'format' => 'block',
          'error' => isset($error) ? $error : null,
          'success' => isset($success) ? $success : null,
          'message' => isset($message) ? $message : null,
      ]);  

      echo $innerContent;

      View::element('footer_required');
    ?>
    <script type="text/javascript" src="<?= $view->getThemePath(); ?>/js/app.js"></script>
  </body>
</html>