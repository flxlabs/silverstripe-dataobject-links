<?php
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\HtmlEditorConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

HtmlEditorConfig::get('cms')->enablePlugins([
	'sslinkdataobject' => ModuleLoader::getModule('flxlabs/silverstripe-dataobject-links')
		->getResource('client/dist/js/TinyMCE_sslink-dataobject.js')
]);

/**
 * Register handler for our shortcodes
 */
ShortcodeParser::get('default')->register(
	'dataobject_link',
	array(FLXLabs\DataObjectLink\DataObjectLinkExtension::class, 'link_shortcode_handler')
);
