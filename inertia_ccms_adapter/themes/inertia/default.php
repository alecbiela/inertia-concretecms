<?php defined('C5_EXECUTE') or die('Access Denied'); ?>
<!DOCTYPE html>
<html lang="<?= Localization::activeLanguage(); ?>">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= $this->getThemePath(); ?>/css/app.bundle.css" type="text/css" />
    <?php
      View::element('header_required', 'inertia_ccms_adapter');
    ?>
    <?php 
      /* Replaces @inertiaHead */
      if (!isset($__inertiaSsrDispatched)) {
          $__inertiaSsrDispatched = true;
          $__inertiaSsrResponse = Core::make(\Inertia\Ssr\Gateway::class)->dispatch($page);
      }

      if ($__inertiaSsrResponse) echo $__inertiaSsrResponse->head;
    ?>
  </head>
  <body>
    <div class='<?= $c->getPageWrapperClass()?>'>
      <?php
          /* Replaces @inertia */
          if (!isset($__inertiaSsrDispatched)) {
              $__inertiaSsrDispatched = true;
              $__inertiaSsrResponse = Core::make(\Inertia\Ssr\Gateway::class)->dispatch($page);
          }

          if ($__inertiaSsrResponse) {
              echo $__inertiaSsrResponse->body;
          } else {
              ?>
                  <div id="<?= $rootView; ?>" data-page='<?= json_encode($page); ?>'></div>
              <?php
          }
      ?>
    </div>
    <?php View::element('footer_required', 'inertia_ccms_adapter'); ?>
    <script type="module" src="<?= $this->getThemePath(); ?>/js/app.bundle.js"></script>
  </body>
</html>