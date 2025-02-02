<?php
if (!defined('MEDIAWIKI')) {
	exit;
}

define('DEBUG', @$_ENV['DEBUG'] == '1');

// Never display errors to the user. We can find them in php-fpm logs still.
// TODO: Change back when warning in CLI is fixed
// error_reporting(DEBUG || PHP_SAPI == 'cli' ? E_ALL : 0);
error_reporting(DEBUG || PHP_SAPI == 'cli' ? E_ALL & ~E_WARNING : 0);

$wgShowExceptionDetails = true;

if (DEBUG) {
	// DO NOT set DEBUG in production!
	$wgDebugLogFile = '/tmp/mediawiki.log';
	$wgDBerrorLog = '/tmp/mediawiki.log';
	$wgDebugComments = true;
	$wgDebugDumpSql = true;
	$wgDebugToolbar = true;
	$wgDevelopmentWarnings = true;

	// Force clear opcaches on every request
	apcu_clear_cache();
	opcache_reset();
}


// Uncomment when running maintenance (e.g. MediaWiki updates)
// $wgReadOnly = PHP_SAPI == 'cli' ? false : 'We’re performing server maintenance. Thanks for your patience. Reach out to us at https://theapplewiki.com/discord if you have any questions.';


$wikis = [
	'theapplewiki.com' => 'applewiki',
	'applewiki.test'   => 'testwiki',
	'wiki.kirb.me'     => 'kirbwiki'
];

$wgConf->wikis = array_values($wikis);

if (defined('MW_DB')) {
	// Automatically set from --wiki option to maintenance scripts
	$wikiID = MW_DB;
} else {
	// Use MW_DB environment variable or map the domain name
	$wikiID = $_SERVER['MW_DB'] ?? $wikis[$_SERVER['SERVER_NAME'] ?? ''] ?? null;
	if (!$wikiID) {
		die("Invalid host\n");
	}
}


// Legacy browsers: Mostly defined as those that don’t support CSS Grid.
// Accessed over HTTP, or Cloudflare rewrite rule passes ?__legacy_browser=1
define('IS_LEGACY', @$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'http' || @$_GET['__legacy_browser'] == '1');


switch ($wikiID) {
case 'applewiki':
	$hostname = 'theapplewiki.com';
	$wgServer = 'https://theapplewiki.com';
	$wgSitename = 'The Apple Wiki';
	$wgMetaNamespace = 'The_Apple_Wiki';
	$wgCitizenThemeColor = '#585858';
	$wgAppleTouchIcon = "$wgResourceBasePath/apple-touch-icon.png";
	$wgLogos = [
		'1x' => $wgAppleTouchIcon,
		'svg' => "$wgResourceBasePath/resources/$wikiID/logo-square.svg",
		'wordmark' => [
			'src' => "$wgResourceBasePath/resources/$wikiID/logo-wordmark.svg",
			'width' => 135,
			'height' => 23
		],
		'icon' => "$wgResourceBasePath/resources/$wikiID/logo-glyph.svg",
	];
	break;

case 'testwiki':
	$hostname = 'applewiki.test';
	$wgServer = 'http://applewiki.test';
	$wgSitename = 'Test Wiki';
	$wgMetaNamespace = 'The_Apple_Wiki';
	$wgCitizenThemeColor = '#585858';
	break;

case 'kirbwiki':
	$hostname = 'wiki.kirb.me';
	$wgServer = '//wiki.kirb.me';
	$wgCanonicalServer = 'https://wiki.kirb.me';
	$wgSitename = 'kirbwiki';
	$wgMetaNamespace = 'kirbwiki';
	$wgCitizenThemeColor = '#fd7423';
	$wgAppleTouchIcon = "$wgResourceBasePath/resources/$wikiID/logo-big.jpg";
	$wgLogos = [
		'1x' => "$wgResourceBasePath/resources/$wikiID/logo.jpg",
		'2x' => $wgAppleTouchIcon,
		'icon' => $wgAppleTouchIcon
	];
	break;
}

// Paths
$wgScriptPath         = '';
$wgArticlePath        = '/wiki/$1';
$wgResourceBasePath   = $wgScriptPath;
$wgUploadPath         = "/images/$wikiID";

$wgCacheDirectory     = "/tmp/mediawiki_cache/$wikiID";
$wgUploadDirectory    = "$IP/images/$wikiID";
$wgUseFileCache       = !DEBUG;
$wgFileCacheDirectory = "$IP/cache";

// Email
// UPO means: this is also a user preference option
$wgEnableEmail         = $wikiID != 'testwiki';
$wgEnableUserEmail     = $wikiID != 'testwiki'; # UPO
// $wgAllowHTMLEmail      = true; // Broken - https://phabricator.wikimedia.org/T383343

$wgEmergencyContact    = "wiki@$hostname";
$wgPasswordSender      = "wiki@$hostname";

$wgEnotifUserTalk      = true; # UPO
$wgEnotifWatchlist     = true; # UPO
$wgEmailAuthentication = true;

$wgSendGridAPIKey = $_ENV['WG_SENDGRID_API_KEY'];

// Database
$wgLocalDatabases = $wgConf->wikis;

$wgDBtype         = 'mysql';
$wgDBserver       = 'database';
$wgDBname         = $wikiID;
$wgDBuser         = 'wikiuser';
$wgDBpassword     = $_ENV['WG_DB_PASSWORD'];
$wgDBprefix       = '';
$wgDBTableOptions = 'ENGINE=InnoDB, DEFAULT CHARSET=binary';

$wgSharedTables[] = 'actor';

// Caching
$wgMainCacheType    = 'redis';
$wgSessionCacheType = 'redis';
$wgMessageCacheType = 'redis';
$wgParserCacheType  = 'redis';
$wgLanguageConverterCacheType = 'redis';

$wgObjectCaches['redis'] = [
	'class'       => 'RedisBagOStuff',
	'servers'     => ['redis:6379'],
	'persistent'  => true,
];

$wgJobTypeConf['default'] = [
	'class'       => 'JobQueueRedis',
	'redisServer' => 'redis:6379',
	'redisConfig' => [],
	'order'       => 'fifo',
	'checkDelay'  => true,
	'claimTTL'    => 60 * 60, // 1 hour
	'daemonized'  => true
];

$wgJobQueueAggregator = [
	'class'       => 'JobQueueAggregatorRedis',
	'redisServer' => 'localhost',
	'redisConfig' => [],
];

$wgObjectCacheSessionExpiry = 24 * 60 * 60; // 1 day
$wgParserCacheExpireTime = 60 * 60; // 1 hour
$wgRevisionCacheExpiry   = 60 * 60; // 1 hour
$wgTranscludeCacheExpiry = 24 * 60 * 60; // 1 day
$wgEnableSidebarCache    = !DEBUG;
$wgUseLocalMessageCache  = !DEBUG;
$wgMiserMode             = true;
$wgQueryCacheLimit       = 10000;

// Uploads
$wgEnableUploads    = true;
$wgUseImageMagick   = true;
$wgImageMagickConvertCommand = '/usr/bin/convert';

// Enable SVG
$wgFileExtensions[] = 'svg';
$wgSVGConverter     = 'rsvg';

// Prefer sending SVG to client rather than rendered PNG
$wgSVGNativeRendering = !IS_LEGACY;

// InstantCommons allows wiki to use images from https://commons.wikimedia.org
$wgUseInstantCommons = true;

// Lazy load images
$wgNativeImageLazyLoading = true;

// Don't generate thumbnails on page load, thumb_handler.php will do it asynchronously
$wgGenerateThumbnailOnParse = false;

// Opt out of Wikimedia analytics
$wgPingback = false;

// Fix double redirects after a move
$wgFixDoubleRedirects = true;

// Show protection indicators
$wgEnableProtectionIndicators = true;

// Site language and timezone
$wgLanguageCode  = 'en';
$wgLocaltimezone = 'UTC';

$wgSecretKey = $_ENV['WG_SECRET_KEY'];

// Changing this will log out all existing sessions.
$wgAuthenticationTokenVersion = $_ENV['WG_AUTHENTICATION_TOKEN_VERSION'];

// License
$wgRightsPage = "$wgMetaNamespace:Copyrights";
$wgRightsUrl  = 'https://creativecommons.org/licenses/by-sa/4.0/';
$wgRightsText = 'Creative Commons Attribution-ShareAlike';

if (IS_LEGACY) {
	$wgRightsIcon = "$wgResourceBasePath/resources/assets/licenses/cc-by-sa.png";
} else {
	$wgRightsIcon = "$wgResourceBasePath/resources/common/cc-by-sa.svg";
}

// Diff tool
$wgDiff3 = '/usr/bin/diff3';

// Namespaces
define('NS_MODULE',      828);
define('NS_MODULE_TALK', 829);

$wgContentNamespaces = [NS_MAIN];

$wgNamespacesToBeSearchedDefault = [
	NS_MAIN     => true,
	NS_PROJECT  => true,
	NS_HELP     => true,
	NS_CATEGORY => true
];

$wgNamespacesWithSubpages[NS_MAIN] = true;

$wgSitemapNamespaces = [NS_MAIN, NS_USER, NS_PROJECT, NS_HELP, NS_CATEGORY];

if ($wikiID == 'applewiki' || $wikiID == 'testwiki') {
	define('NS_KEYS',            2304);
	define('NS_KEYS_TALK',       2305);
	define('NS_DEV',             2306);
	define('NS_DEV_TALK',        2307);
	define('NS_FILESYSTEM',      2308);
	define('NS_FILESYSTEM_TALK', 2309);

	$wgExtraNamespaces += [
		NS_DEV             => 'Dev',
		NS_DEV_TALK        => 'Dev_talk',
		NS_FILESYSTEM      => 'Filesystem',
		NS_FILESYSTEM_TALK => 'Filesystem_talk'
	];

	$wgContentNamespaces += [NS_KEYS, NS_DEV, NS_FILESYSTEM];
	$wgSitemapNamespaces += [NS_KEYS, NS_DEV, NS_FILESYSTEM];

	$wgNamespacesToBeSearchedDefault += [
		NS_DEV        => true,
		NS_FILESYSTEM => true
	];

	$wgNamespacesWithSubpages += [
		NS_DEV        => true,
		NS_FILESYSTEM => true
	];
}

$wgPageImagesNamespaces = $wgContentNamespaces;

$wgAvailableRights += ['edittemplate'];
$wgRestrictionLevels = ['autoconfirmed', 'bot', 'edittemplate', 'editinterface', 'sysop'];
$wgCascadingRestrictionLevels = ['autoconfirmed', 'bot', 'edittemplate', 'editinterface', 'sysop'];

$wgNamespaceProtection[NS_MODULE] = ['editinterface', 'edittemplate'];

// Extensions
wfLoadExtension('Parsoid', "$IP/vendor/wikimedia/parsoid/extension.json");
wfLoadExtensions([
	'AbuseFilter',
	'AdvancedSearch',
	'AntiSpoof',
	'CategoryTree',
	'CheckUser',
	'CirrusSearch',
	'Cite',
	'CodeEditor',
	'CodeMirror',
	'CommonsMetadata',
	'ConfirmEdit',
	'ConfirmEdit/hCaptcha',
	'Details',
	'Disambiguator',
	'DiscordRCFeed',
	'DiscussionTools',
	'Echo',
	'Elastica',
	'EventLogging',
	'Gadgets',
	'InputBox',
	'Interwiki',
	'Linter',
	'LoginNotify',
	'MultimediaViewer',
	'MultiPurge',
	'Nuke',
	'OATHAuth',
	'OAuth',
	'PageImages',
	'ParserFunctions',
	'ParserMigration',
	'Popups',
	'RelatedArticles',
	'ReplaceText',
	'RevisionSlider',
	'Scribunto',
	'SendGrid',
	'SpamBlacklist',
	'SyntaxHighlight_GeSHi',
	'TabberNeue',
	'TemplateData',
	'TemplateStyles',
	'TemplateStylesExtender',
	'TemplateWizard',
	'TextExtracts',
	'Thanks',
	'UploadWizard',
	'VisualEditor',
	'WikiEditor',
	'WikiSEO',
]);

if ($wikiID == 'applewiki' || $wikiID == 'testwiki') {
	wfLoadExtensions([
		'SemanticMediaWiki',
		'SemanticResultFormats',
		'SemanticScribunto'
	]);

	if (file_exists("$IP/extensions/KeyPages")) {
		wfLoadExtension('KeyPages');
	}
}

// Skins
wfLoadSkins([
	'Citizen',
	'MinervaNeue',
	'MonoBook',
	'Vector',
]);

$legacySkin = file_exists("$IP/skins/MonoBookLegacy") ? 'MonoBookLegacy' : 'Vector';
if ($legacySkin == 'MonoBookLegacy') {
	wfLoadSkin('MonoBookLegacy');
}

$wgDefaultSkin = IS_LEGACY ? $legacySkin : 'citizen';

// Parsoid
$wgParsoidSettings = [
	'useSelser' => true,
	'linting'   => true
];

$wgParserEnableLegacyMediaDOM = PHP_SAPI != 'cli' && !IS_LEGACY;
$wgParserEnableLegacyHeadingDOM = PHP_SAPI != 'cli' && !IS_LEGACY;

// Reverse proxy
$wgUseCdn            = true;
$wgUsePrivateIPs     = true;
$wgCdnServersNoPurge = ['172.0.0.0/8'];
$wgCdnMaxAge         = 24 * 60 * 60; // 1 day
$wgForcedRawSMaxage  = 15 * 60; // 15 mins
$wgCdnMatchParameterOrder = true;

// Add <link rel="canonical">
$wgEnableCanonicalServerLink = true;

// Cookies
$wgSecureLogin       = $wikiID == 'kirbwiki';
$wgCookieSecure      = $wikiID != 'testwiki';
$wgCookieSameSite    = 'Lax';

// Security headers
$wgBreakFrames       = true;
$wgReferrerPolicy    = ['strict-origin-when-cross-origin', 'strict-origin'];
$wgCSPHeader = [
	'default-src'     => ['\'self\''],
	'script-src'      => ['\'self\'', 'https://static.cloudflareinsights.com'],
	'object-src'      => ['\'none\''],
	'frame-ancestors' => ['\'none\''],
	'upgrade-insecure-requests' => true,
	'block-all-mixed-content' => true,
	'disown-opener'   => true,
	'report-uri'      => false
];
$wgApiFrameOptions   = 'SAMEORIGIN';

// Captcha
$wgHCaptchaSiteKey      = $_ENV['WG_HCAPTCHA_SITE_KEY'];
$wgHCaptchaSecretKey    = $_ENV['WG_HCAPTCHA_SECRET_KEY'];
$wgHCaptchaSendRemoteIP = true;

// DNS denylist
$wgEnableDnsBlacklist = $wikiID != 'testwiki';
$wgDnsBlacklistUrls   = ['xbl.spamhaus.org.', 'rbl.dnsbl.im.', 'noptr.spamrats.com.', 'all.s5h.net.', 'z.mailspike.net.'];
$wgSuspiciousIpExpiry = 24 * 60 * 60; // 1 day

// User CSS/JS
$wgAllowUserCss = true;
$wgAllowUserJs  = true;

// Allow site CSS (not user CSS) on UserLogin and Preferences
$wgAllowSiteCSSOnRestrictedPages = true;

// Transclude from Wikipedia
$wgEnableScaryTranscluding = true;

// Permissions

$star_perms = $wgGroupPermissions['*'];

// Everyone
$wgGroupPermissions['*'] = [
	'createaccount' => false,
	'read' => true
];

if ($wikiID == 'applewiki') {
	// Set to false in an emergency
	$wgGroupPermissions['*']['createaccount'] = true;
}

// Logged in user
$wgGroupPermissions['user'] += $star_perms;
$wgGroupPermissions['user']['edit']   = false;
$wgGroupPermissions['user']['upload'] = false;
$wgGroupPermissions['user']['editcontentmodel'] = false;

// Don't let users create users
$wgRevokePermissions['user']['createaccount'] = true;

// Logged in user with confirmed email
$wgGroupPermissions['emailconfirmed'] = [
	'edit'   => true,
	'mwoauthproposeconsumer' => true,
	'upload' => true
];

// Logged in user who has made sufficient edits
$wgAutoConfirmAge   = 4 * 24 * 60 * 60; // 4 days
$wgAutoConfirmCount = 20;
$wgGroupPermissions['autoconfirmed'] = [
	'autoconfirmed' => true,
	'editcontentmodel'  => true,
	'editsemiprotected' => true,
	'reupload'      => true,
	'sendemail'     => true,
	'skipcaptcha'   => true,
	'upload_by_url' => true
];

// Trusted
$wgGroupPermissions['trusted'] = $wgGroupPermissions['autoconfirmed'] + [
	'apihighlimits'    => true,
	'autopatrol'       => true,
	'edittemplate'     => true,
	'ipblock-exempt'   => true,
	'mergehistory'     => true,
	'move'             => true,
	'move-categorypages' => true,
	'move-subpages'    => true,
	'movefile'         => true,
	'noratelimit'      => true,
	'patrol'           => true,
	'purge'            => true,
	'reupload-shared'  => true,
	'suppressredirect' => true
];

// Bots
$wgGroupPermissions['bot'] += $wgGroupPermissions['trusted'];

// Interface admin
$wgGroupPermissions['interface-admin'] = [
	'editusercss'    => true,
	'edituserjson'   => true,
	'edituserjs'     => true,
	'editsitecss'    => true,
	'editsitejson'   => true,
	'editsitejs'     => true,
	'editinterface'  => true,
	'edittemplate'   => true
];

// Moderator
$wgPrivilegedGroups[] = 'moderator';
$wgGroupPermissions['moderator'] = $wgGroupPermissions['trusted'] + [
	'abusefilter-hidden-log'   => true,
	'abusefilter-hide-log'     => true,
	'abusefilter-log-detail'   => true,
	'abusefilter-log-private'  => true,
	'abusefilter-modify-blocked-external-domains' => true,
	'abusefilter-modify-restricted' => true,
	'abusefilter-modify'       => true,
	'abusefilter-revert'       => true,
	'abusefilter-view-private' => true,
	'block'          => true,
	'blockemail'     => true,
	'browsearchive'  => true,
	'createaccount'  => true,
	'delete'         => true,
	'deletedhistory' => true,
	'deletedtext'    => true,
	'editprotected'  => true,
	'hideuser'       => true,
	'import'         => true,
	'importupload'   => true,
	'interwiki'      => true,
	'markbotedits'   => true,
	'move-rootuserpages' => true,
	'noratelimit'    => true,
	'protect'        => true,
	'purge'          => true,
	'renameuser'     => true,
	'replacetext'    => true,
	'rollback'       => true,
	'smw-pageedit'   => true,
	'smw-schemaedit' => true,
	'undelete'       => true,
	'unwatchedpages' => true,
	'viewsuppressed' => true
];

// Un-revoke for mod/admin
$wgRevokePermissions['moderator']['createaccount'] = false;

// Admins
$wgGroupPermissions['sysop'] += $wgGroupPermissions['moderator'] + $wgGroupPermissions['interface-admin'] + [
	'checkuser-log'       => true,
	'checkuser'           => true,
	'createaccount'       => true,
	'edit'                => true,
	'interwiki'           => true,
	'mwoauthmanageconsumer'  => true,
	'mwoauthproposeconsumer' => true,
	'purge'               => true,
	'smw-admin'           => true,
	'smw-pageedit'        => true,
	'smw-schemaedit'      => true,
];

// Un-revoke for mod/admin
$wgRevokePermissions['sysop']['createaccount'] = false;

// Delete unneeded groups
$merge_to_sysop = ['bureaucrat', 'checkuser', 'checkuser-temporary-account-viewer', 'suppress', 'smwadministrator', 'smwcurator', 'smweditor', 'push-subscription-manager', 'upwizcampeditors'];
foreach ($merge_to_sysop as $group) {
	if (isset($wgGroupPermissions[$group])) {
		$wgGroupPermissions['sysop'] = $wgGroupPermissions[$group] + $wgGroupPermissions['sysop'];
	}

	unset($wgGroupPermissions[$group]);
	unset($wgRevokePermissions[$group]);
	unset($wgAddGroups[$group]);
	unset($wgRemoveGroups[$group]);
	unset($wgGroupsAddToSelf[$group]);
	unset($wgGroupsRemoveFromSelf[$group]);
}

$wgExtensionFunctions[] = function() use (&$wgGroupPermissions) {
	global $merge_to_sysop;
	foreach ($merge_to_sysop as $group) {
		if (isset($wgGroupPermissions[$group])) {
			$wgGroupPermissions['sysop'] = $wgGroupPermissions[$group] + $wgGroupPermissions['sysop'];
		}

		unset($wgGroupPermissions[$group]);
	}
};

// Require email address when signing up
$wgEmailConfirmToEdit = true;

// Block creating accounts using the API
$wgAPIModules['createaccount'] = 'ApiDisabled';

// Auto grant emailconfirmed group when email is confirmed (and remove when not)
$wgAutopromote['emailconfirmed'] = APCOND_EMAILCONFIRMED;

// Require confirmed email as a prerequisite of being autoconfirmed
$wgAutopromote['autoconfirmed'][] = APCOND_EMAILCONFIRMED;

// Require minimum password length
$wgPasswordPolicy['policies']['default']['MinimalPasswordLength'] = 10;

// Rate limits

// 10 account creations per week (captcha failures are counted as a creation)
$wgAccountCreationThrottle = [
	[
		'count'   => 10,
		'seconds' => 60 * 60 * 24 * 7 // 1 week
	]
];

// Popups
$wgPopupsReferencePreviewsBetaFeature = false;

// DiscordRCFeed
if ($wikiID == 'applewiki') {
	$wgRCFeeds['discord-applewiki'] = [
		'url' => $_ENV['WG_DISCORD_WEBHOOK_APPLEWIKI'],
		'omit_bots' => true,
		'style' => 'embed'
	];
	$wgRCFeeds['discord-hackdifferent'] = [
		'url' => $_ENV['WG_DISCORD_WEBHOOK_HACKDIFFERENT'],
		'omit_bots' => true,
		'omit_log_types' => ['block', 'newusers', 'patrol'],
		'style' => 'embed'
	];
} else if ($wikiID == 'testwiki') {
	$wgRCFeeds['discord-testwiki'] = [
		'url' => $_ENV['WG_DISCORD_WEBHOOK_APPLEWIKI'],
		'omit_bots' => false,
		'style' => 'embed'
	];
}

// Article count behavior ({{NUMBEROFARTICLES}} etc)
$wgArticleCountMethod = 'any';

// Don't run jobs on web requests, we do them via cron
$wgRunJobsAsync = false;
$wgJobRunRate   = 0;

// Echo
$wgEchoUseJobQueue = true;

// ParserFunctions
$wgPFEnableStringFunctions = true;

// ReplaceText
$wgReplaceTextResultsLimit = 1000;

// Scribunto
$wgScribuntoDefaultEngine = 'luasandbox';

// Semantic MediaWiki
if (function_exists('enableSemantics')) {
	enableSemantics($hostname);
}

$smwgPDefaultType         = '_txt';
$smwgQueryResultCacheType = 'redis';
$smwgQueryResultCacheLifetime = 6 * 60 * 60; // 6 hours
$smwgEnabledQueryDependencyLinksStore = true;
$smwgQFilterDuplicates    = true;
$smwgChangePropagationProtection = false;

if ($wikiID == 'applewiki' || $wikiID == 'testwiki') {
	$smwgNamespacesWithSemanticLinks[NS_KEYS]       = true;
	$smwgNamespacesWithSemanticLinks[NS_DEV]        = true;
	$smwgNamespacesWithSemanticLinks[NS_FILESYSTEM] = true;
}

// Use custom subtle MediaWiki icon
$wgFooterIcons['poweredby']['mediawiki']['src'] = "$wgResourceBasePath/resources/common/poweredby-mediawiki.svg";

// Disable SMW icon
$wgFooterIcons['poweredby']['semanticmediawiki'] = [];

// WikiSEO
$wgWikiSeoEnableAutoDescription = true;
$wgWikiSeoOverwritePageImage    = true;
$wgWikiSeoSocialImageShowLogo   = true;
$wgWikiSeoSocialImageShowAuthor = false;

if ($wikiID != 'kirbwiki') {
	$wgWikiSeoDefaultImage = "$wgServer$wgResourceBasePath/resources/$wikiID/banner.png";
}

// Citizen
$wgCitizenShowPageTools = 'permission';
$wgCitizenSearchGateway = 'mwRestApi';
$wgCitizenSearchDescriptionSource = 'textextracts';
$wgCitizenMaxSearchResults = 10;

// Vector
$wgVectorResponsive = true;

// Fully enable dark mode and enable auto mode by default
$wgVectorNightMode['beta'] = true;
$wgVectorNightMode['logged_out'] = true;
$wgVectorNightMode['logged_in'] = true;
$wgDefaultUserOptions['vector-theme'] = 'os';

// RelatedArticles
$wgRelatedArticlesFooterAllowedSkins[] = 'citizen';
$wgRelatedArticlesFooterAllowedNamespaces = [NS_MAIN];
$wgRelatedArticlesUseCirrusSearchApiUrl = "$wgScriptPath/api.php";
$wgRelatedArticlesDescriptionSource   = 'textextracts';
$wgRelatedArticlesUseCirrusSearch     = true;
$wgRelatedArticlesOnlyUseCirrusSearch = true;
$wgRelatedArticlesCardLimit           = 3;

if ($wikiID == 'applewiki' || $wikiID == 'testwiki') {
	$wgRelatedArticlesFooterAllowedNamespaces[] = NS_DEV;
	$wgRelatedArticlesFooterAllowedNamespaces[] = NS_FILESYSTEM;
}

// TextExtracts
$wgExtractsExtendOpenSearchXml = true;

// PageImages
$wgPageImagesExpandOpenSearchXml = true;

// WikiEditor
$wgWikiEditorRealtimePreview = true;

// VisualEditor
$wgVisualEditorEnableBetaFeature = true;
$wgVisualEditorEnableWikitextBetaFeature = true;
$wgVisualEditorEnableDiffPageBetaFeature = true;
$wgVisualEditorUseSingleEditTab  = true;

// Allow selecting how long to store a title in the watchlist
$wgWatchlistExpiry = true;

if ($wikiID == 'applewiki' || $wikiID == 'testwiki') {
	// Footer links
	$wgHooks['SkinAddFooterLinks'][] = function($skin, $key, &$footerLinks) {
		if ($key == 'places') {
			$footerLinks['disclaimers'] = Html::element('a', ['href' => '/wiki/The_Apple_Wiki:Ground_rules'], 'Ground rules');
		}
	};
}

// Footer credits
$wgMaxCredits = 2;

// Known URL schemes that can be auto-linked
$wgUrlProtocols = ['//', 'http://', 'https://', 'ftp://', 'ftps://'];

// Link to GitHub commits on Special:Version
$wgGitRepositoryViewers['https://github.com/(.*?)(.git)?'] = 'https://github.com/$1/commit/%H';

// Edit Recovery feature flag - subject to change after 1.41
// https://www.mediawiki.org/wiki/Manual:Edit_Recovery
$wgEnableEditRecovery = true;
$wgDefaultUserOptions['editrecovery'] = 1;

// CirrusSearch
$wgSearchType = 'CirrusSearch';
$wgCirrusSearchServers = ['elasticsearch'];
$wgCirrusSearchIndexBaseName = $wikiID;
$wgCirrusSearchUseCompletionSuggester = 'yes';
$wgCirrusSearchCompletionSuggesterSubphrases = [
	'build' => true,
	'use'   => true,
	'type'  => 'anywords',
	'limit' => 10
];
$wgCirrusSearchWikimediaExtraPlugin['regex'] = [
	'build' => true,
	'use'   => true,
	'max_inspect' => 10000
];

// MultiPurge
$wgMultiPurgeEnabledServices = $wikiID == 'testwiki' ? [] : ['Cloudflare'];
$wgMultiPurgeServiceOrder = ['Cloudflare'];
$wgMultiPurgeCloudFlareZoneId = $_ENV['WG_CLOUDFLARE_ZONE'];
$wgMultiPurgeCloudFlareApiToken = $_ENV['WG_CLOUDFLARE_TOKEN'];
$wgMultiPurgeRunInQueue = true;
$wgMultiPurgeStaticPurges = [
	'Startup Script' => 'load.php?lang=en&modules=startup&only=scripts&raw=1&skin=citizen',
	'Site Styles'    => 'load.php?lang=en&modules=site.styles&only=styles&skin=citizen'
];

// Hack to fix MultiPurge not using absolute urls for images
// https://github.com/octfx/mediawiki-extensions-MultiPurge/issues/4
$wgHooks['MultiPurgeOnPurgeUrls'][] = function(&$urls) {
	global $wgServer;
	for ($i = 0; $i < count($urls); $i++) {
		if ($urls[$i][0] == '/') {
			$urls[$i] = $wgServer . $urls[$i];
		}
	}
};

// UploadWizard
$wgAllowCopyUploads = true;
$wgUploadNavigationUrl = str_replace('$1', 'Special:UploadWizard', $wgArticlePath);
$wgUploadWizardConfig = json5_decode(file_get_contents('/uploadwizard.json'), true);
$wgUploadWizardConfig['debug'] = DEBUG;

if (isset($_ENV['WG_FLICKR_API_KEY'])) {
	$wgCopyUploadsDomains = ['*.flickr.com', '*.staticflickr.com'];
	$wgUploadWizardConfig['flickrApiKey'] = $_ENV['WG_FLICKR_API_KEY'];
}

// CommonsMetadata
$wgCommonsMetadataSetTrackingCategories = true;
