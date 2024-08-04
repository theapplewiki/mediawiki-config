<?php
if (!defined('MEDIAWIKI')) {
	exit;
}


// Never display errors to the user. We can find them in apache logs still.
error_reporting(PHP_SAPI == 'cli' ? E_ALL : 0);
// error_reporting(E_ALL);


$wgShowExceptionDetails = true;
$wgDebugLogFile = '/tmp/mediawiki.log';


// Uncomment when running maintenance (e.g. MediaWiki updates)
// $wgReadOnly = PHP_SAPI == 'cli' ? false : 'We’re performing server maintenance. Thanks for your patience. Reach out to us at https://theapplewiki.com/discord if you have any questions.';


$wikis = [
	'theapplewiki.com' => 'applewiki'
];

$wgConf->settings = [
	'hostname' => [
		'applewiki' => 'theapplewiki.com'
	],

	'wgServer' => [
		'applewiki' => 'https://theapplewiki.com'
	],

	'wgSitename' => [
		'applewiki' => 'The Apple Wiki'
	],

	'wgMetaNamespace' => [
		'applewiki' => 'The_Apple_Wiki'
	],

	'wgCitizenThemeColor' => [
		'applewiki' => '#585858'
	]
];

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

extract($wgConf->getAll($wikiID));

// Paths
$wgScriptPath         = '';
$wgArticlePath        = '/wiki/$1';
$wgResourceBasePath   = $wgScriptPath;
$wgUploadPath         = "/images/$wikiID";

$wgCacheDirectory     = "/tmp/mediawiki_cache/$wikiID";
$wgUploadDirectory    = "$IP/images/$wikiID";
$wgUseFileCache       = true;
$wgFileCacheDirectory = "$IP/cache";

// Logos
$wgLogos = [
	'1x' => "$wgResourceBasePath/apple-touch-icon.png",
	'svg' => "$wgResourceBasePath/resources/$wikiID/logo-square.svg",
	'wordmark' => [
		'src' => "$wgResourceBasePath/resources/$wikiID/logo-wordmark.svg",
		'width' => 135,
		'height' => 23
	],
	'icon' => "$wgResourceBasePath/resources/$wikiID/logo-glyph.svg",
];
$wgAppleTouchIcon = "$wgResourceBasePath/apple-touch-icon.png";

// Email
// UPO means: this is also a user preference option
$wgEnableEmail         = true;
$wgEnableUserEmail     = true; # UPO
$wgAllowHTMLEmail      = true;

$wgEmergencyContact    = "wiki@$hostname";
$wgPasswordSender      = "wiki@$hostname";

$wgEnotifUserTalk      = true; # UPO
$wgEnotifWatchlist     = true; # UPO
$wgEmailAuthentication = true;

$wgSendGridAPIKey = $_ENV['WG_SENDGRID_API_KEY'];

// Database
$wgLocalDatabases = $wgConf->wikis = array_values($wikis);

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
	'servers'     => ['redis:6379']
];

$wgJobTypeConf['default'] = [
	'class'       => 'JobQueueRedis',
	'redisServer' => 'redis:6379',
	'redisConfig' => [],
	'claimTTL'    => 3600,
	'daemonized'  => true
];

$wgObjectCacheSessionExpiry = 24 * 60 * 60; // 1 day
$wgParserCacheExpireTime = 30 * 60; // 30 mins
$wgTranscludeCacheExpiry = 24 * 60 * 60; // 1 day
$wgEnableSidebarCache    = true;
$wgUseLocalMessageCache  = true;
$wgMiserMode             = true;

// Uploads
$wgEnableUploads    = true;
$wgUseImageMagick   = true;
$wgImageMagickConvertCommand = '/usr/bin/convert';

// Enable SVG
$wgFileExtensions[] = 'svg';
$wgSVGConverter     = 'rsvg';

// Prefer sending SVG to client rather than rendered PNG
$wgSVGNativeRendering = true;

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

// Site language and timezone
$wgLanguageCode  = 'en';
$wgLocaltimezone = 'UTC';

$wgSecretKey = $_ENV['WG_SECRET_KEY'];

// Changing this will log out all existing sessions.
$wgAuthenticationTokenVersion = $_ENV['WG_AUTHENTICATION_TOKEN_VERSION'];

// License
$wgRightsPage = ''; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl  = 'https://creativecommons.org/licenses/by-sa/4.0/';
$wgRightsText = 'Creative Commons Attribution-ShareAlike';
$wgRightsIcon = "$wgResourceBasePath/resources/applewiki/cc-by-sa-v2.svg";

// Diff tool
$wgDiff3 = '/usr/bin/diff3';

// Namespaces
define('NS_MODULE',          828);
define('NS_MODULE_TALK',     829);
define('NS_KEYS',            2304);
define('NS_KEYS_TALK',       2305);
define('NS_DEV',             2306);
define('NS_DEV_TALK',        2307);
define('NS_FILESYSTEM',      2308);
define('NS_FILESYSTEM_TALK', 2309);

$wgExtraNamespaces[NS_KEYS]            = 'Keys';
$wgExtraNamespaces[NS_KEYS_TALK]       = 'Keys_talk';
$wgExtraNamespaces[NS_DEV]             = 'Dev';
$wgExtraNamespaces[NS_DEV_TALK]        = 'Dev_talk';
$wgExtraNamespaces[NS_FILESYSTEM]      = 'Filesystem';
$wgExtraNamespaces[NS_FILESYSTEM_TALK] = 'Filesystem_talk';

$wgContentNamespaces = [NS_MAIN, NS_KEYS, NS_DEV, NS_FILESYSTEM];
$wgNamespacesToBeSearchedDefault = [
	NS_MAIN       => true,
	NS_PROJECT    => true,
	NS_DEV        => true,
	NS_FILESYSTEM => true
];

$wgNamespacesWithSubpages[NS_MAIN]       = true;
$wgNamespacesWithSubpages[NS_DEV]        = true;
$wgNamespacesWithSubpages[NS_FILESYSTEM] = true;

$wgSitemapNamespaces    = [NS_MAIN, NS_USER, NS_PROJECT, NS_HELP, NS_CATEGORY, NS_KEYS, NS_DEV, NS_FILESYSTEM];
$wgPageImagesNamespaces = $wgContentNamespaces;

// TODO: Uncomment when we’re in a better position to do this
// $wgNamespaceProtection[NS_TEMPLATE] = ['editinterface', 'edittemplate'];
$wgNamespaceProtection[NS_MODULE]   = ['edittemplate'];

// Extensions
wfLoadExtension('Parsoid', "$IP/vendor/wikimedia/parsoid/extension.json");
wfLoadExtension('AbuseFilter');
wfLoadExtension('AdvancedSearch');
wfLoadExtension('AntiSpoof');
wfLoadExtension('CategoryTree');
wfLoadExtension('CheckUser');
wfLoadExtension('Cite');
wfLoadExtension('CirrusSearch');
wfLoadExtension('CodeEditor');
wfLoadExtension('CodeMirror');
wfLoadExtension('ConfirmEdit');
wfLoadExtension('ConfirmEdit/hCaptcha');
wfLoadExtension('Details');
wfLoadExtension('DiscordRCFeed');
wfLoadExtension('Elastica');
wfLoadExtension('Gadgets');
wfLoadExtension('InputBox');
wfLoadExtension('Interwiki');
wfLoadExtension('Linter');
wfLoadExtension('MediaWikiAuth');
wfLoadExtension('MultimediaViewer');
wfLoadExtension('OATHAuth');
wfLoadExtension('OAuth');
wfLoadExtension('PageImages');
wfLoadExtension('ParserFunctions');
wfLoadExtension('ParserMigration');
wfLoadExtension('Popups');
wfLoadExtension('RelatedArticles');
wfLoadExtension('Renameuser');
wfLoadExtension('ReplaceText');
wfLoadExtension('Scribunto');
wfLoadExtension('SemanticMediaWiki');
wfLoadExtension('SemanticScribunto');
wfLoadExtension('SendGrid');
wfLoadExtension('SpamBlacklist');
wfLoadExtension('SyntaxHighlight_GeSHi');
wfLoadExtension('TabberNeue');
wfLoadExtension('TemplateData');
wfLoadExtension('TemplateStyles');
wfLoadExtension('TemplateStylesExtender');
wfLoadExtension('TextExtracts');
wfLoadExtension('VisualEditor');
wfLoadExtension('WikiEditor');
wfLoadExtension('WikiSEO');

// Skins
wfLoadSkin('MinervaNeue');
wfLoadSkin('MonoBook');
wfLoadSkin('Vector');
wfLoadSkin('Citizen');

$wgDefaultSkin = 'citizen';

// Parsoid
$wgParsoidSettings = [
	'useSelser' => true,
	'linting'   => true
];

$wgParserEnableLegacyMediaDOM = false;

// Reverse proxy
$wgUseCdn            = true;
$wgUsePrivateIPs     = true;
$wgCdnServersNoPurge = ['0.0.0.0/0'];
$wgCdnMaxAge         = 60 * 10;
$wgForcedRawSMaxage  = 60 * 30;

// Add <link rel="canonical">
$wgEnableCanonicalServerLink = true;

// Cookies
$wgCookieSecure      = true;
$wgCookieSameSite    = 'Lax';

// Security headers
$wgBreakFrames       = true;
$wgReferrerPolicy    = ['strict-origin-when-cross-origin', 'strict-origin'];
$wgCSPHeader = [
	'default-src'     => ['\'self\''],
	'object-src'      => ['\'none\''],
	'frame-ancestors' => ['\'none\''],
	'upgrade-insecure-requests' => true,
	'block-all-mixed-content' => true,
	'disown-opener'   => true
];

// Captcha
$wgHCaptchaSiteKey      = $_ENV['WG_HCAPTCHA_SITE_KEY'];
$wgHCaptchaSecretKey    = $_ENV['WG_HCAPTCHA_SECRET_KEY'];
$wgHCaptchaSendRemoteIP = true;

// DNS denylist
$wgEnableDnsBlacklist = true;
$wgDnsBlacklistUrls   = ['zen.spamhaus.org.', 'spam.dnsbl.sorbs.net.', 'rbl.dnsbl.im.', 'noptr.spamrats.com.', 'all.s5h.net.', 'z.mailspike.net.'];
$wgSuspiciousIpExpiry = 60 * 60; // 1 hour

// User CSS/JS
$wgAllowUserCss = true;
$wgAllowUserJs  = true;

// Allow site CSS (not user CSS) on UserLogin and Preferences
$wgAllowSiteCSSOnRestrictedPages = true;

// Transclude from Wikipedia
$wgEnableScaryTranscluding = true;

// Permissions

// Everyone
// Uncomment the following in an emergency
// $wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit']   = false;
$wgGroupPermissions['*']['purge']  = false;
$wgGroupPermissions['*']['upload'] = false;

// Logged in user
$wgGroupPermissions['user']['edit']      = false;
$wgGroupPermissions['user']['sendemail'] = false;
$wgGroupPermissions['user']['upload']    = false;

// Don't let users create users
$wgGroupPermissions['user']['createaccount'] = false;

// Logged in user with confirmed email
$wgGroupPermissions['emailconfirmed']['createaccount'] = false;
$wgGroupPermissions['emailconfirmed']['edit']       = true;
$wgGroupPermissions['emailconfirmed']['upload']     = true;

// Logged in user who has made sufficient edits
$wgAutoConfirmAge   = 3 * 24 * 60 * 60;
$wgAutoConfirmCount = 20;
$wgGroupPermissions['autoconfirmed']['createaccount'] = false;
$wgGroupPermissions['autoconfirmed']['sendemail']     = true;
$wgGroupPermissions['autoconfirmed']['skipcaptcha']   = true;

// Autopatrolled
$wgGroupPermissions['autopatrolled'] = $wgGroupPermissions['autoconfirmed'];
$wgGroupPermissions['autopatrolled']['autopatrol']   = true;

// Trusted
$wgGroupPermissions['trusted'] = $wgGroupPermissions['autopatrolled'];
$wgGroupPermissions['trusted']['edittemplate']  = true;
$wgGroupPermissions['trusted']['editinterface'] = true;
$wgGroupPermissions['trusted']['edittemplate']  = true;
$wgGroupPermissions['trusted']['move']          = true;
$wgGroupPermissions['trusted']['patrol']        = true;

// Bots
$wgGroupPermissions['bot']['createaccount'] = false;
$wgGroupPermissions['bot']['edit']          = true;
$wgGroupPermissions['bot']['mwoauthproposeconsumer'] = true;
$wgGroupPermissions['bot']['skipcaptcha']   = true;
$wgGroupPermissions['bot']['upload']        = true;

// Admins
$wgGroupPermissions['sysop']['checkuser-log']       = true;
$wgGroupPermissions['sysop']['checkuser']           = true;
$wgGroupPermissions['sysop']['createaccount']       = true;
$wgGroupPermissions['sysop']['edit']                = true;
$wgGroupPermissions['sysop']['editinterface']       = true;
$wgGroupPermissions['sysop']['edittemplate']        = true;
$wgGroupPermissions['sysop']['interwiki']           = true;
$wgGroupPermissions['sysop']['investigate']         = true;
$wgGroupPermissions['sysop']['mwa-createlocalaccount'] = true;
$wgGroupPermissions['sysop']['mwoauthmanageconsumer'] = true;
$wgGroupPermissions['sysop']['mwoauthproposeconsumer'] = true;
$wgGroupPermissions['sysop']['purge']               = true;
$wgGroupPermissions['sysop']['smw-admin']           = true;
$wgGroupPermissions['sysop']['smw-pageedit']        = true;
$wgGroupPermissions['sysop']['smw-schemaedit']      = true;

// Delete unneeded groups
foreach (['bureaucrat', 'checkuser', 'suppress', 'smwadministrator', 'smwcurator', 'smweditor'] as $group) {
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
	foreach (['bureaucrat', 'checkuser', 'suppress', 'smwadministrator', 'smwcurator', 'smweditor'] as $group) {
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
		'seconds' => 60 * 60 * 24 * 7
	]
];

// Popups
$wgPopupsReferencePreviewsBetaFeature = false;

// DiscordRCFeed
$wgRCFeeds['discord-applewiki'] = [
	'url' => $_ENV['WG_DISCORD_WEBHOOK_APPLEWIKI'],
// 	'omit_bots' => true
];
$wgRCFeeds['discord-hackdifferent'] = [
	'url' => $_ENV['WG_DISCORD_WEBHOOK_HACKDIFFERENT'],
	'omit_bots' => true,
	'omit_log_types' => ['block', 'newusers', 'patrol']
];

// MediaWikiAuth
$wgMediaWikiAuthApiUrl = 'https://www.theiphonewiki.com/w/api.php';
$wgMediaWikiAuthDisableAccountCreation = true;

// Article count behavior ({{NUMBEROFARTICLES}} etc)
$wgArticleCountMethod = 'any';

// Don't run jobs on web requests, we do them via cron
$wgRunJobsAsync = false;
$wgJobRunRate   = 0;

// ParserFunctions
$wgPFEnableStringFunctions = true;

// ReplaceText
$wgReplaceTextResultsLimit = 1000;

// Scribunto
$wgScribuntoDefaultEngine = 'luasandbox';

// Semantic MediaWiki
enableSemantics($hostname);

$smwgPDefaultType         = '_txt';
$smwgQueryResultCacheType = 'redis';
$smwgQueryResultCacheLifetime = 30 * 60; // 30 mins
$smwgEnabledQueryDependencyLinksStore = true;
$smwgQFilterDuplicates    = true;
$smwgChangePropagationProtection = false;

$smwgNamespacesWithSemanticLinks[NS_KEYS]       = true;
$smwgNamespacesWithSemanticLinks[NS_DEV]        = true;
$smwgNamespacesWithSemanticLinks[NS_FILESYSTEM] = true;

// Override to use SVG file rather than (ugh) inline base64 PNG
// $wgFooterIcons['poweredby']['semanticmediawiki'] = [
// 	'src' => "$wgResourceBasePath/resources/applewiki/poweredby-smw.svg",
// 	'url' => 'https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki',
// 	'alt' => 'Powered by Semantic MediaWiki',
// 	'class' => 'smw-footer'
// ];
$wgFooterIcons['poweredby']['semanticmediawiki'] = [];

// Use custom subtle MediaWiki icon
$wgFooterIcons['poweredby']['mediawiki']['src'] = "$wgResourceBasePath/resources/applewiki/poweredby-mediawiki.svg";

// WikiSEO
$wgWikiSeoEnableAutoDescription = true;
$wgWikiSeoOverwritePageImage    = true;
$wgWikiSeoSocialImageShowLogo   = true;
$wgWikiSeoSocialImageShowAuthor = false;
$wgWikiSeoDefaultImage = "$wgServer$wgResourceBasePath/resources/$wikiID/banner.png";

// Citizen
$wgCitizenShowPageTools = 'permission';
$wgCitizenSearchGateway = 'mwRestApi';
$wgCitizenSearchDescriptionSource = 'textextracts';
$wgCitizenMaxSearchResults = 10;

// RelatedArticles
$wgRelatedArticlesFooterAllowedSkins[] = 'citizen';
$wgRelatedArticlesFooterAllowedNamespaces = [NS_MAIN, NS_DEV, NS_FILESYSTEM];
$wgRelatedArticlesUseCirrusSearchApiUrl = "$wgScriptPath/api.php";
$wgRelatedArticlesDescriptionSource   = 'textextracts';
$wgRelatedArticlesUseCirrusSearch     = true;
$wgRelatedArticlesOnlyUseCirrusSearch = true;

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

// Exclude key pages from Special:Random, except if specifically requested (Special:Random/Keys)
$wgHooks['RandomPageQuery'][] = function(&$tables, &$conds, &$joinConds) {
	if ($conds['page_namespace'] != [NS_KEYS]) {
		$conds[] = 'page_namespace != ' . NS_KEYS;
	}
};

// Exclude key pages from Special:WantedPages
$wgHooks['WantedPages::getQueryInfo'][] = function(&$wantedPages, &$query) {
	$query['conds'][] = 'pl_namespace != ' . NS_KEYS;
};

// Footer links
$wgHooks['SkinAddFooterLinks'][] = function($skin, $key, &$footerLinks) {
	if ($key == 'places') {
		$footerLinks['disclaimers'] = Html::element('a', ['href' => '/wiki/The_Apple_Wiki:Ground_rules'], 'Ground rules');
	}
};

// Footer credits
$wgMaxCredits = 2;

// Known URL schemes that can be auto-linked
$wgUrlProtocols = ['http://', 'https://', 'ftp://', 'ftps://'];

// Link to GitHub commits on Special:Version
$wgGitRepositoryViewers['https://github.com/(.*?)(.git)?'] = 'https://github.com/$1/commit/%H';

// Edit Recovery feature flag - subject to change after 1.41
// https://www.mediawiki.org/wiki/Manual:Edit_Recovery
// TODO: Users didn’t like this on 1.41 because it lacks the ability to discard previous changes,
// re-enable with 1.42
// $wgEnableEditRecovery = true;
// $wgDefaultUserOptions['editrecovery'] = 1;

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
