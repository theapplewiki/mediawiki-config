<h2 align="center">
<img src="https://github.githubassets.com/images/icons/emoji/unicode/1f33b.png">
<br>
The Apple Wiki’s MediaWiki config files
</h2>

This repository holds the configuration used by [The Apple Wiki](https://theapplewiki.com/), a community wiki built on MediaWiki.

This is not intended to be a general-purpose MediaWiki config repository, but rather a way to track any changes we make to the wiki config, and any issues/feature requests. However, you may still find this useful to look at if you run MediaWiki yourself.

If you have any questions, catch us [on Discord](https://theapplewiki.com/discord).

## The setup

On the host, we use:

* nginx
* PHP 8.2
* [jobrunner](https://github.com/wikimedia/mediawiki-services-jobrunner)
* certbot
* s3cmd for backups

In containers, we use:

* MediaWiki 1.43
* php-fpm
* MariaDB
* Redis
* ElasticSearch

Services we use:

* Cloudflare
* S3
* hCaptcha
* SendGrid
* Sentry

Currently, we run on a single server. This seems to manage our traffic perfectly fine.

Extensions installed, beyond ones in the base MediaWiki install:

* Admin
  * [AntiSpoof](https://www.mediawiki.org/wiki/Extension:AntiSpoof)*
  * [CheckUser](https://www.mediawiki.org/wiki/Extension:CheckUser)*
  * [OAuth](https://www.mediawiki.org/wiki/Extension:OAuth)*
  * [Renameuser](https://www.mediawiki.org/wiki/Extension:Renameuser)
* Editing
  * [CodeMirror](https://www.mediawiki.org/wiki/Extension:CodeMirror)*
  * [TemplateWizard](https://www.mediawiki.org/wiki/Extension:TemplateWizard)*
* Housekeeping
  * [Disambiguator](https://www.mediawiki.org/wiki/Extension:Disambiguator)*
  * [DiscordRCFeed](https://www.mediawiki.org/wiki/Extension:DiscordRCFeed)
  * [KeyPages](https://github.com/theapplewiki/mediawiki-extensions-KeyPages)
  * [MultiPurge](https://www.mediawiki.org/wiki/Extension:MultiPurge)
  * [ParserMigration](https://www.mediawiki.org/wiki/Extension:ParserMigration)*
  * [SendGrid](https://www.mediawiki.org/wiki/Extension:SendGrid)
  * [WikiSEO](https://www.mediawiki.org/wiki/Extension:WikiSEO)
* Page rendering
  * [Details](https://www.mediawiki.org/wiki/Extension:Details)
  * [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto)*
  * [TabberNeue](https://www.mediawiki.org/wiki/Extension:TabberNeue)
  * [TemplateStyles](https://www.mediawiki.org/wiki/Extension:TemplateStyles)*
  * [TemplateStylesExtender](https://www.mediawiki.org/wiki/Extension:TemplateStylesExtender)
* Search
  * [AdvancedSearch](https://www.mediawiki.org/wiki/Extension:AdvancedSearch)*
  * [CirrusSearch](https://www.mediawiki.org/wiki/Extension:CirrusSearch)*
  * [Elastica](https://www.mediawiki.org/wiki/Extension:Elastica)*
* Skins
  * [Citizen](https://github.com/StarCitizenTools/mediawiki-skins-Citizen)
* [Semantic MediaWiki](https://www.semantic-mediawiki.org/)
  * [Semantic Result Formats](https://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats)
  * [Semantic Scribunto](https://www.mediawiki.org/wiki/Extension:Semantic_Scribunto)
* UI
  * [Popups](https://www.mediawiki.org/wiki/Extension:Popups)*
  * [RelatedArticles](https://www.mediawiki.org/wiki/Extension:RelatedArticles)*
  * [RevisionSlider](https://www.mediawiki.org/wiki/Extension:RevisionSlider)*

\* [Also used by WMF](https://www.mediawiki.org/wiki/Category:Extensions_used_on_Wikimedia).

We have all extensions and skins cloned into html/extensions/ and html/skins/ via git. This allows them to be updated easily with `./update-exts.sh`.

If the MediaWiki image needs to be built, or rebuilt, use `./update-mw.sh`.

## Patches

We currently manually patch the following:

### TemplateStylesExtender

This should probably be considered for upstreaming. This fixes (or at least silences) a type error that I haven't fully understood the cause of yet.

```patch
diff --git a/includes/Matcher/VarNameMatcher.php b/includes/Matcher/VarNameMatcher.php
index eebd370..fb24bf1 100644
--- a/includes/Matcher/VarNameMatcher.php
+++ b/includes/Matcher/VarNameMatcher.php
@@ -35,7 +35,7 @@ class VarNameMatcher extends Matcher {
 		$len = count( $values );

 		for ( $i = $start; $i < $len; $i++ ) {
-			if ( preg_match( '/^\s*--[\w-]+\s*$/', $values[$i]->value() ) === 1 ) {
+			if ( preg_match( '/^\s*--[\w-]+\s*$/', (string)$values[$i]->value() ) === 1 ) {
 				yield $this->makeMatch( $values, $start, $this->next( $values, $start, $options ) );
 			}
 		}
```

## To do

* Move nginx to a container

## License

Licensed under the Apache License, version 2.0. Refer to [LICENSE.md](LICENSE.md).
