<?php

namespace FLxLabs\DataObjectLink;

use SilverStripe\Admin\Forms\LinkFormFactory;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\HiddenField;

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
                _t(__CLASS__.'.SELECT_OBJECT', 'Select a type'),
                $context['AllowedClasses'],
                str_replace("_", "\\", $context['ClassName'])
            )
                ->setHasEmptyDefault(true),
            TextField::create(
                'Description',
                _t(__CLASS__.'.LINKDESCR', 'Link description'),
                $context['Description']
            ),
            CheckboxField::create(
                'TargetBlank',
                _t(__CLASS__.'.LINKOPENNEWWIN', 'Open in new window/tab'),
                $context['TargetBlank']
            ),
        ]);

        if ($context['ClassName']) {
            $fields->insertAfter(
                'ClassName',
                DropdownField::create(
                    'ObjectID',
                    _t(__CLASS__.'.SELECT_OBJECT', 'Select an object'),
                    str_replace("_", "\\", $context['ClassName'])::get()->Map('ID', 'Title'),
                    $context['ObjectID']
                )->setHasEmptyDefault(true)
            );
        }

        if ($context['RequireLinkText']) {
            $fields->insertBefore(
                'Description', 
                TextField::create('Text', _t(__CLASS__.'.LINKTEXT', 'Link text'))
            );
        }

        return $fields;
    }

    protected function getValidator($controller, $name, $context)
    {
        if ($context['RequireLinkText']) {
            return RequiredFields::create('ClassName', 'ObjectID', 'Text');
        }

        return RequiredFields::create('ClassName', 'ObjectID');
    }
}
