<?php
/**
 * Custom footer/base template to use with Inertia page template
 * Removes unnecessary features such as page edit mode/design buttons
 * @inheritdoc concrete/elements/footer_required.php
 */
use Concrete\Core\Localization\Localization;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\User;
use Concrete\Core\View\View;
use Concrete\Core\Support\Facade\Element;

defined('C5_EXECUTE') or die('Access Denied.');

$app = Application::getFacadeApplication();
$c = Page::getCurrentPage();
$site = $app->make('site')->getSite();
$config = $site->getConfigRepository();
$localization = Localization::getInstance();
$cp = is_object($c) ? new Permissions($c) : null;

if ($cp !== null) {
    $localization->pushActiveContext(Localization::CONTEXT_UI);
    try {
        View::element('inertia_page_controls_footer', ['cp' => $cp, 'c' => $c]);
    } finally {
        $localization->popActiveContext();
    }
}

if (empty($disableTrackingCode)) {
    echo $config->get('seo.tracking.code.footer');
}

View::getInstance()->markFooterAssetPosition();

if (!isset($display_account_menu)) {
    $display_account_menu = $config->get('user.display_account_menu');
}

if ($display_account_menu) {
    if ($cp === null || !$cp->canViewToolbar()) {
        $u = $app->make(User::class);
        if ($u->isRegistered()) {
            if (!$dh->inDashboard($c)) {
                $localization->pushActiveContext(Localization::CONTEXT_UI);
                try {
                    View::element('account/menu');
                } finally {
                    $localization->popActiveContext();
                }
            }
        }
    }
}