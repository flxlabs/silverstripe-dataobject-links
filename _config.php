<?php
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\TinyMCE\TinyMCEConfig;

// Register TinyMCE plugin
call_user_func(function () {
	$editorConfig = HTMLEditorConfig::get('cms');
	
	if (!$editorConfig instanceof TinyMCEConfig) {
		return;
	}
	
	$editorConfig->enablePlugins([
		'sslinkdataobject' => ModuleLoader::getModule('flxlabs/silverstripe-dataobject-links')
			->getResource('client/dist/js/TinyMCE_sslink-dataobject.js')
	]);
});

/**
 * Register handler for our shortcodes
 */
ShortcodeParser::get('default')->register(
	'dataobject_link',
	array(FLXLabs\DataObjectLink\DataObjectLinkExtension::class, 'link_shortcode_handler')
);
