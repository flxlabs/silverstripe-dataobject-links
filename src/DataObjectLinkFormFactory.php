<?php

namespace FLxLabs\DataObjectLink;

use SilverStripe\Admin\Forms\LinkFormFactory;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;

/**
 * Provides a form factory for inserting dataobject links in a HTML editor
 */
class DataObjectLinkFormFactory extends LinkFormFactory
{
	/**
	 * @param RequestHandler $controller
	 * @param string $name
	 * @param array $context
	 * @return FieldList
	 */
	protected function getFormFields($controller, $name, $context)
	{
		// Get classes config
		$classes = Config::inst()->get(
			DataObjectLinkModalExtension::class,
			'classes',
			Config::EXCLUDE_EXTRA_SOURCES
		);
		if (!$classes) {
			$classes = [];
		}
		$sanitizeClasses = [];
		foreach ($classes as $cl => $data) {
			$key = str_replace('\\', '_', $cl ?? '');
			$sanitizeClasses[$key] = $data['name'];
		}

		// Get values from request (for AJAX updates) or context (for initial load)
		$request = $controller->getRequest();
		$className = $request->getVar('ClassName') ?: ($context['ClassName'] ?? null);
		$objId = $request->getVar('ObjectID') ?: ($context['ObjectID'] ?? null);
		$dependantObjId = $request->getVar('DependantObjectID') ?: ($context['DependantObjectID'] ?? null);
		$descr = $request->getVar('Description') ?: ($context['Description'] ?? null);
		$targetBlank = $request->getVar('TargetBlank') ?: ($context['TargetBlank'] ?? null);
		
		// Cast IDs to integers if they're numeric strings
		if ($objId && is_numeric($objId)) {
			$objId = (int)$objId;
		}
		if ($dependantObjId && is_numeric($dependantObjId)) {
			$dependantObjId = (int)$dependantObjId;
		}

		$fields = FieldList::create([
			DropdownField::create(
				'ClassName',
				_t(__CLASS__ . '.SELECT_OBJECT', 'Select a type'),
				$sanitizeClasses,
				$className ? str_replace("_", "\\", $className) : null
			)
				->setHasEmptyDefault(true),
			TextField::create(
				'Description',
				_t(__CLASS__ . '.LINKDESCR', 'Link description'),
				$descr
			),
			CheckboxField::create(
				'TargetBlank',
				_t(__CLASS__ . '.LINKOPENNEWWIN', 'Open in new window/tab'),
				$targetBlank
			),
		]);

		if ($className) {
			$classNameFull = str_replace("_", "\\", $className ?? '');
			$classConfig = $this->getClassConfig($classNameFull);
			$titleField = 'Title';
			$rc = singleton($classNameFull);
			$sort = "Title";

			if ($classConfig && isset($classConfig["sort"]) && $classConfig["sort"]) {
				$sort = $classConfig["sort"];
			}
			if ($classConfig && isset($classConfig["display_field"]) && $classConfig["display_field"]) {
				$titleField = $classConfig["display_field"];
			}
			$values = $classNameFull::get()->sort($sort);
			if ($classConfig && isset($classConfig["filter"]) && $classConfig["filter"]) {
				$values = $values->where($classConfig["filter"]);
			}
			$fields->insertAfter(
				'ClassName',
				DropdownField::create(
					'ObjectID',
					_t(__CLASS__ . '.SELECT_OBJECT', 'Select an object'),
					$values->Map('ID', $titleField),
					$objId
				)->setHasEmptyDefault(true)
			);
		}

		if ($objId) {
			// Check if there is a dependant class
			$classNameFull = str_replace("_", "\\", $className ?? '');
			$dependantClassConfig = $this->getClassConfig($classNameFull);
			if ($dependantClassConfig && isset($dependantClassConfig["dependant_class"])) {
				$titleField = 'Title';
				$rc = singleton($classNameFull);

				if ($rc->hasMethod("getObjectSelectorTitle")) {
					$titleField = 'getObjectSelectorTitle';
				}

				$objects = $dependantClassConfig["dependant_class"]::get()->filter([$dependantClassConfig["dependant_field"] => $objId])->Map('ID', $titleField);
				$fields->insertAfter(
					'ObjectID',
					DropdownField::create(
						'DependantObjectID',
						_t(__CLASS__ . '.SELECT_DEPENDANT_OBJECT', 'Select a dependant object'),
						$objects,
						$dependantObjId
					)->setHasEmptyDefault(true)
				);
			}
		}

		if ($context['RequireLinkText']) {
			$fields->insertBefore(
				'Description',
				TextField::create('Text', _t(__CLASS__ . '.LINKTEXT', 'Link text'))
			);
		}

		$this->extend('updateFormFields', $fields, $controller, $name, $context);

		return $fields;
	}

	protected function getValidator($controller, $name, $context)
	{
		if ($context['RequireLinkText']) {
			return RequiredFieldsValidator::create('ClassName', 'ObjectID', 'Text');
		}

		return RequiredFieldsValidator::create('ClassName', 'ObjectID');
	}

	protected function getClassConfig($class)
	{
		$classes = Config::inst()->get(
			DataObjectLinkModalExtension::class,
			'classes',
			Config::EXCLUDE_EXTRA_SOURCES
		);

		return $classes[$class] ?? null;
	}
}
