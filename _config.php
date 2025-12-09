<?php

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\HtmlEditorConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

/** @var \SilverStripe\TinyMCE\TinyMCEConfig */
$config = HtmlEditorConfig::get('cms');
$config->enablePlugins([
	'sslinkdataobject' => ModuleLoader::getModule('flxlabs/silverstripe-dataobject-links')
		->getResource('client/dist/js/TinyMCE_sslink-dataobject.js')
]);

/**
 * Register handler for our shortcodes
 */
ShortcodeParser::get('default')->register(
	'dataobject_link',
	array(FLXLabs\DataObjectLink\DataObjectLinkParser::class, 'link_shortcode_handler')
);
