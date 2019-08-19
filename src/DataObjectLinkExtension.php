<?php

namespace FLxLabs\DataObjectLink;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

class DataObjectLinkExtension extends Extension
{
	public function updateClientConfig(&$clientConfig)
	{
		$clientConfig['form']['editorDataObjectLink'] = [
			'schemaUrl' => $this->getOwner()->Link('methodSchema/Modals/editorDataObjectLink')
		];
	}

	public static function link_shortcode_handler($arguments, $content = null, $parser = null)
	{
		if (!isset($arguments['id']) || !is_numeric($arguments['id']) || !isset($arguments['clazz'])) {
			return null;
		}

		$class = str_replace('_', '\\', $arguments['clazz']);

		if (
			!($obj = DataObject::get_by_id($class, $arguments['id']))
			&& !($obj = Versioned::get_latest_version($class, $arguments['id']))
		) {
			return null; // There were no suitable matches at all.
		}

		$link = Convert::raw2att($obj->Link());

		if ($content) {
			return sprintf('<a href="%s">%s</a>', $link, $parser->parse($content));
		} else {
			return $link;
		}
	}
}
