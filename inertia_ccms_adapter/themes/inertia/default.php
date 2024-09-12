<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>
<!DOCTYPE html>
<html lang="<?php echo Localization::activeLanguage() ?>">
  <head>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <link rel="stylesheet" type="text/css" href="/concrete/themes/elemental/css/bootstrap-modified.css">
      <?php
      View::element('header_required', [
          'pageTitle' => isset($pageTitle) ? $pageTitle : '',
          'pageDescription' => isset($pageDescription) ? $pageDescription : '',
          'pageMetaKeywords' => isset($pageMetaKeywords) ? $pageMetaKeywords : ''
      ]);
      ?>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <div class="<?php echo $c->getPageWrapperClass()?>">
      <main>
          <?php
          $a = new Area('Main');
          $a->enableGridContainer();
          $a->display($c);

          $a = new Area('Page Footer');
          $a->enableGridContainer();
          $a->display($c);
          ?>
      </main>
      <footer id="concrete5-brand">
          <div class="container">
              <div class="row">
                  <div class="col-sm-12">
                      <span><?php echo t('Built with <a href="https://www.concretecms.org" class="concrete5" rel="nofollow">Concrete CMS</a>.') ?></span>
                      <span class="pull-right">
                          <?php echo Core::make('helper/navigation')->getLogInOutLink() ?>
                      </span>
                      <span id="ccm-account-menu-container"></span>
                  </div>
              </div>
          </div>
      </footer>
      </div>
  <?php View::element('footer_required'); ?>
  <script type="text/javascript" src="<?=$view->getThemePath()?>/main.js"></script>
  </body>
</html>
