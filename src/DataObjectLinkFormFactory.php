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
		$fields = FieldList::create([
			DropdownField::create(
				'ClassName',
				_t(__CLASS__ . '.SELECT_OBJECT', 'Select a type'),
				$context['AllowedClasses'],
				str_replace("_", "\\", $context['ClassName'] ?? '')
			)
				->setHasEmptyDefault(true),
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

		if ($context['ClassName']) {
			$className = str_replace("_", "\\", $context['ClassName'] ?? '');
			$classConfig = $this->getClassConfig($className);
			$titleField = 'Title';
			$rc = singleton($className);
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
			$fields->insertAfter(
				'ClassName',
				DropdownField::create(
					'ObjectID',
					_t(__CLASS__ . '.SELECT_OBJECT', 'Select an object'),
					$values->Map('ID', $titleField),
					$context['ObjectID']
				)->setHasEmptyDefault(true)
			);
		}

		if ($context['ObjectID']) {
			// Check if there is a dependant class
			$dependantClass = $this->getClassConfig(str_replace("_", "\\", $context['ClassName'] ?? ''));
			if ($dependantClass && $dependantClass["dependant_class"]) {
				$titleField = 'Title';

				if ($rc->hasMethod("getObjectSelectorTitle")) {
					$titleField = 'getObjectSelectorTitle';
				}

				$objects = $dependantClass["dependant_class"]::get()->filter([$dependantClass["dependant_field"] => $context['ObjectID']])->Map('ID', $titleField);
				$fields->insertAfter(
					'ObjectID',
					DropdownField::create(
						'DependantObjectID',
						_t(__CLASS__ . '.SELECT_DEPENDANT_OBJECT', 'Select a dependant object'),
						$objects,
						$context['DependantObjectID']
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

		return $classes[$class];
	}
}
