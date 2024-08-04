# The Apple Wiki’s MediaWiki config files

This repository holds the configuration used by [The Apple Wiki](https://theapplewiki.com/), a community wiki built on MediaWiki.

This is not intended to be a general-purpose MediaWiki config repository, but rather a way to track any changes we make to the wiki config, and any issues/feature requests. However, you may still find this useful to look at if you run MediaWiki yourself.

If you have any questions, catch us [on Discord](https://theapplewiki.com/discord).

## The setup

On the host, we use:

* nginx
* PHP 8.1
* [jobrunner](https://github.com/wikimedia/mediawiki-services-jobrunner)
* certbot
* s3cmd for backups

In containers, we use:

* MediaWiki 1.41
* php-fpm
* MariaDB
* Redis
* ElasticSearch

Services we use:

* Cloudflare
* S3
* hCaptcha
* SendGrid

Currently, we run on a single server. This seems to manage our traffic perfectly fine.

Major extensions installed:

* [CirrusSearch](https://www.mediawiki.org/wiki/Extension:CirrusSearch)
* [Citizen](https://github.com/StarCitizenTools/mediawiki-skins-Citizen)
* [Gadgets](https://www.mediawiki.org/wiki/Extension:Gadgets)
* [ParserFunctions](https://www.mediawiki.org/wiki/Extension:ParserFunctions)
* [RelatedArticles](https://www.mediawiki.org/wiki/Extension:RelatedArticles)
* [Scribunto](https://www.mediawiki.org/wiki/Extension:Scribunto)
* [Semantic MediaWiki](https://www.semantic-mediawiki.org/)
* [TemplateStyles](https://www.mediawiki.org/wiki/Extension:TemplateStyles) and [TemplateStylesExtender](https://www.mediawiki.org/wiki/Extension:TemplateStylesExtender)
* [VisualEditor](https://www.mediawiki.org/wiki/Extension:VisualEditor)

We also use [MediaWikiAuth](https://www.mediawiki.org/wiki/Extension:MediaWikiAuth) to allow users to migrate an account from [The iPhone Wiki](https://www.theiphonewiki.com/)

We have all extensions and skins cloned into html/extensions/ and html/skins/ via git. This allows them to be updated easily with `./update-exts.sh`.

If the MediaWiki image needs to be built, or rebuilt, use `./update-mw.sh`.

## To do
* Move jobrunner to a container
* Move nginx to a container
* Break up `applewiki`-specific LocalSettings.php config to a separate file

## License

Licensed under the Apache License, version 2.0. Refer to [LICENSE.md](LICENSE.md).
