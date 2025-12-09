<?php

namespace FLxLabs\DataObjectLink;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;

class DataObjectLinkParser
{
	public static function link_shortcode_handler($arguments, $content = null, $parser = null)
	{
		if (!isset($arguments['id']) || !is_numeric($arguments['id']) || !isset($arguments['clazz'])) {
			return null;
		}

		$class = str_replace('_', '\\', $arguments['clazz'] ?? '');
		if (!class_exists($class)) {
			return null;
		}

		if (!($obj = DataObject::get($class)->byID($arguments['id']))) {
			if (class_exists('SilverStripe\Versioned\Versioned')) {
				$obj = \SilverStripe\Versioned\Versioned::get_latest_version($class, $arguments['id']);
			}
			if (!$obj) {
				return null; // There were no suitable matches at all.
			}
		}

		$link = Convert::raw2att($obj->Link());

		if ($content) {
			return sprintf('<a href="%s">%s</a>', $link, $parser->parse($content));
		} else {
			return $link;
		}
	}
}
