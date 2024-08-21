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

    <!-- @vite('resources/js/app.js') -->
    <?php 
      /* Replaces @inertiaHead */
      $view->inc('elements/inertiaHead.php', array('pageSSR'=>$pageSSR)); 
    ?>
  </head>
  <body>
    <?php
        /* Replaces @inertia */
        // TODO: Add replacement for $expression to customize SSR app root, for now ID is always 'app'
        $id = 'app'; 
        if (!isset($__inertiaSsrDispatched)) {
            $__inertiaSsrDispatched = true;
            $__inertiaSsrResponse = Core::make(\InertiaConcrete\Ssr\Gateway::class)->dispatch($pageSSR);
        }

        if ($__inertiaSsrResponse) {
            echo $__inertiaSsrResponse->body;
        } else {
            ?>
                <div id="<?= $id; ?>" data-page="<?= json_encode($pageSSR); ?>"></div>
            <?php
        }

      View::element('footer_required');
    ?>
    <script type="text/javascript" src="<?= $view->getThemePath(); ?>/js/app.js"></script>
  </body>
</html>