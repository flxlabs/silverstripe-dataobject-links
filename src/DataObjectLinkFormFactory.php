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
	 * @param array $context
	 * @return array
	 */
	protected function updateContext($controller, $context)
	{
		$request = $controller->getRequest();
		$showLinkText = $request->getVar('requireLinkText');

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

		$text = $request->getVar('Text');
		$class = $request->getVar('ClassName');
		$objId = $request->getVar('ObjectID');
		$descr = $request->getVar('Description');
		$targetBlank = $request->getVar('TargetBlank');

		return [
			'RequireLinkText' => isset($showLinkText) || isset($text),
			'AllowedClasses' => $sanitizeClasses,
			'ClassName' => $class ? $class : null,
			'ObjectID' => $objId ? (int) $objId : null,
			'Description' => $descr ? $descr : null,
			'TargetBlank' => $targetBlank ? $targetBlank : null,
			...$context,
		];
	}

	protected function getOptions($context)
	{
		if (!$context['ClassName']) {
			return [];
		}

		$className = str_replace("_", "\\", $context['ClassName'] ?? '');
		$classConfig = $this->getClassConfig($className);
		$titleField = 'Title';
		$sort = "Title";

		if (isset($classConfig["sort"]) && $classConfig["sort"]) {
			$sort = $classConfig["sort"];
		}
		if (isset($classConfig["display_field"]) && $classConfig["display_field"]) {
			$titleField = $classConfig["display_field"];
		}
		$values = $className::get()->sort($sort);
		if (isset($classConfig["filter"]) && $classConfig["filter"]) {
			$values = $values->where($classConfig["filter"]);
		}
		return $values->Map('ID', $titleField)->toArray();
	}

	/**
	 * @param RequestHandler $controller
	 * @param string $name
	 * @param array $context
	 * @return FieldList
	 */
	protected function getFormFields($controller, $name, $context)
	{
		$context = $this->updateContext($controller, $context);

		$options = $this->getOptions($context);

		$fields = FieldList::create([
			DropdownField::create(
				'ClassName',
				_t(__CLASS__ . '.SELECT_OBJECT', 'Select a type'),
				$context['AllowedClasses'],
				str_replace("_", "\\", $context['ClassName'] ?? '')
			)
				->setHasEmptyDefault(true)
				->setEmptyString(_t(__CLASS__ . '.SELECT_CLASS_EMPTY', 'Please select...')),
			DropdownField::create(
				'ObjectID',
				_t(__CLASS__ . '.SELECT_OBJECT', 'Select an object'),
				$options,
				$context['ObjectID']
			)->setHasEmptyDefault(true)
				->setEmptyString(_t(__CLASS__ . '.SELECT_OBJECT_EMPTY', 'Please select...')),
			TextField::create(
				'Description',
				_t(__CLASS__ . '.LINKDESCR', 'Link description'),
				$context['Description']
			),
			CheckboxField::create(
				'TargetBlank',
				_t(__CLASS__ . '.LINKOPENNEWWIN', 'Open in new window/tab'),
				$context['TargetBlank']
			),
		]);

		if ($context['RequireLinkText']) {
			$fields->insertBefore(
				'Description',
				TextField::create('Text', _t(__CLASS__ . '.LINKTEXT', 'Link text'))
			);
		}

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
