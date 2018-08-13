<?php
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

call_user_func(function () {
    $module = ModuleLoader::inst()
        ->getManifest()
        ->getModule('flxlabs/silverstripe-dataobjectlink');

    // Enable insert-link to data objects
    TinyMCEConfig::get('cms')
        ->enablePlugins([
            'sslinkdataobject' => $module->getResource('client/dist/js/TinyMCE_sslink-dataobject.js'),
        ]);
});

/**
 * Register handler for our shortcodes
 */
ShortcodeParser::get('default')->register(
    'dataobject_link',
    array(FLXLabs\DataObjectLink\DataObjectLinkExtension::class, 'link_shortcode_handler')
);
