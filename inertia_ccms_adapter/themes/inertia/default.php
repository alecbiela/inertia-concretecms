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
    <?php 
      /* Replaces @inertiaHead */
      if (!isset($__inertiaSsrDispatched)) {
          $__inertiaSsrDispatched = true;
          $__inertiaSsrResponse = Core::make(\Inertia\Ssr\Gateway::class)->dispatch($page);
      }

      if ($__inertiaSsrResponse) {
          echo $__inertiaSsrResponse->head;
      }
    ?>
  </head>
  <body>
    <div class='<?php echo $c->getPageWrapperClass()?>'>
      <?php
          /* Replaces @inertia */
          // TODO: Add replacement for $expression to customize SSR app root, for now ID is always 'app'
          $id = 'app'; 
          if (!isset($__inertiaSsrDispatched)) {
              $__inertiaSsrDispatched = true;
              $__inertiaSsrResponse = Core::make(\Inertia\Ssr\Gateway::class)->dispatch($page);
          }

          if ($__inertiaSsrResponse) {
              echo $__inertiaSsrResponse->body;
          } else {
              ?>
                  <div id="<?= $id; ?>" data-page='<?= json_encode($page); ?>'></div>
              <?php
          }
      ?>
    </div>
    
    <?php View::element('footer_required', 'inertia_ccms_adapter'); ?>
    <script type="module" src="<?= $this->getThemePath(); ?>/js/app.bundle.js"></script>
  </body>
</html>