<?php
/**
 * Custom header template to use with Inertia page template
 * Removes unnecessary features for better use with <Head> component
 * @inheritdoc concrete/elements/footer_required.php
 */
use Concrete\Core\Cookie\ResponseCookieJar;
use Concrete\Core\Url\SeoCanonical;
use Concrete\Core\User\User;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Support\Facade\Application;
use Symfony\Component\EventDispatcher\GenericEvent;

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Arguments:
 * @var bool|null $disableTrackingCode
 */

$c = Page::getCurrentPage();
$cp = false;
$app = Application::getFacadeApplication();
$site = $app->make('site')->getSite();
$config = $site->getConfigRepository();
$appConfig = $app->make('config');

if (is_object($c)) {
    $cp = new Permissions($c);
    $cID = $c->getCollectionID();
} else {
    $cID = 1;
    $c = null;
}
$metaTags = [];
$metaTags['charset'] = sprintf('<meta http-equiv="content-type" content="text/html; charset=%s">', APP_CHARSET);
if ($c !== null && $c->getAttribute('exclude_search_index')) {
    $metaTags['robots'] = sprintf('<meta name="robots" content="%s">', 'noindex');
}
if ($appConfig->get('concrete.misc.generator_tag_display_in_header')) {
    $metaTags['generator'] = sprintf('<meta name="generator" content="%s">', 'Concrete CMS');
}
if (($modernIconFID = (int) $config->get('misc.modern_tile_thumbnail_fid')) && ($modernIconFile = File::getByID($modernIconFID))) {
    $metaTags['msapplication-TileImage'] = sprintf('<meta name="msapplication-TileImage" content="%s">', $modernIconFile->getURL());
    $modernIconBGColor = (string) $config->get('misc.modern_tile_thumbnail_bgcolor');
    if ($modernIconBGColor !== '') {
        $metaTags['msapplication-TileColor'] = sprintf('<meta name="msapplication-TileColor" content="%s">', h($modernIconBGColor));
    }
}
$linkTags = [];
if (($favIconFID = (int) $config->get('misc.favicon_fid')) && ($favIconFile = File::getByID($favIconFID))) {
    $favIconFileURL = $favIconFile->getURL();
    $linkTags['shortcut icon'] = sprintf('<link rel="shortcut icon" href="%s" type="image/x-icon">', $favIconFileURL);
    $linkTags['icon'] = sprintf('<link rel="icon" href="%s" type="image/x-icon">', $favIconFileURL);
}
if (($appleIconFID = (int) $config->get('misc.iphone_home_screen_thumbnail_fid')) && ($appleIconFile = File::getByID($appleIconFID))) {
    $linkTags['apple-touch-icon'] = sprintf('<link rel="apple-touch-icon" href="%s">', $appleIconFile->getURL());
}
$browserToolbarColor = (string) $config->get('misc.browser_toolbar_color');
if ($browserToolbarColor !== '') {
    $metaTags['browserToolbarColor'] = sprintf('<meta name="theme-color" content="%s">', h($browserToolbarColor));
}
if ($config->get('seo.canonical_tag.enabled')) {
    if (($canonicalLink = $app->make(SeoCanonical::class)->getPageCanonicalURLTag($c, Request::getInstance())) !== null) {
        $linkTags['canonical'] = (string) $canonicalLink;
    }
}
$alternateHreflangTags = [];
if ($c !== null && $config->get('multilingual.set_alternate_hreflang') && !$c->isAdminArea() && $app->make('multilingual/detector')->isEnabled()) {
    $multilingualSection = Section::getBySectionOfSite($c);
    if ($multilingualSection) {
        $urlManager = $app->make('url/manager');
        foreach (Section::getList($site) as $ms) {
            $relatedID = $ms->getTranslatedPageID($c);
            if ($relatedID) {
                $relatedPage = Page::getByID($relatedID);
                if ($relatedPage && !$relatedPage->isError()) {
                    $url = $urlManager->resolve([$relatedPage]);
                    $alternateHreflangTags[] = '<link rel="alternate" hreflang="'.str_replace('_', '-', $ms->getLocale()).'" href="'.$url.'">';
                }
            }
        }
    }
}

// Generate and dispatch an event, to let other Add-Ons make use of the available (meta) tags/page title
$event = new GenericEvent();
$event->setArgument('metaTags', $metaTags);
$event->setArgument('linkTags', $linkTags);
$event->setArgument('alternateHreflangTags', $alternateHreflangTags);
$app->make('director')->dispatch('on_header_required_ready', $event);
$metaTags = $event->getArgument('metaTags');
$linkTags = $event->getArgument('linkTags');
$alternateHreflangTags = $event->getArgument('alternateHreflangTags');
?>

<?php
echo implode(PHP_EOL, $metaTags).PHP_EOL;
if (!empty($linkTags)) {
    echo implode(PHP_EOL, $linkTags).PHP_EOL;
}
if (!empty($alternateHreflangTags)) {
    echo implode(PHP_EOL, $alternateHreflangTags).PHP_EOL;
}
?>
<script type="text/javascript">
    var CCM_DISPATCHER_FILENAME = <?= json_encode(DIR_REL . '/' . DISPATCHER_FILENAME, JSON_UNESCAPED_SLASHES) ?>;
    var CCM_CID = <?= (int) $cID ?>;
    var CCM_EDIT_MODE = 'false';
    var CCM_ARRANGE_MODE = 'false';
    var CCM_IMAGE_PATH = <?= json_encode(ASSETS_URL_IMAGES, JSON_UNESCAPED_SLASHES) ?>;
    var CCM_APPLICATION_URL = <?= json_encode(rtrim((string) $app->make('url/canonical'), '/'), JSON_UNESCAPED_SLASHES) ?>;
    var CCM_REL = <?= json_encode((string) $app->make('app_relative_path'), JSON_UNESCAPED_SLASHES) ?>;
    var CCM_ACTIVE_LOCALE = <?= json_encode(Localization::activeLocale(), JSON_UNESCAPED_SLASHES) ?>;
    var CCM_USER_REGISTERED = <?= $app->make(User::class)->isRegistered() ? 'true ': 'false' ?>;
    var CCM_SITE_NAME = '<?= $site->getSiteName(); ?>';
</script>

<?php
$v = View::getRequestInstance();
if ($cp) {
    if (!$v->isEditingDisabled()) {
        View::element('page_controls_header', ['cp' => $cp, 'c' => $c]);
    }
}
$v->markHeaderAssetPosition();
if (empty($disableTrackingCode)) {
    echo $config->get('seo.tracking.code.header');
}
if ($c !== null) {
    echo $c->getAttribute('header_extra_content');
}
