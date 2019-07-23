<?php

namespace FLxLabs\DataObjectLink;

use SilverStripe\Admin\LeftAndMainFormRequestHandler;
use SilverStripe\Admin\ModalController;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Core\Config\Config;

/**
 * Decorates ModalController with insert internal link
 * @see ModalController
 */
class DataObjectLinkModalExtension extends Extension
{
    private static $allowed_actions = array(
        'editorDataObjectLink',
    );

    /**
     * @return ModalController
     */
    public function getOwner()
    {
        /** @var ModalController $owner */
        $owner = $this->owner;
        return $owner;
    }


    /**
     * Form for inserting internal link pages
     *
     * @return Form
     */
    public function editorDataObjectLink()
    {
        $showLinkText = $this->getOwner()->getRequest()->getVar('requireLinkText');

        $factory = DataObjectLinkFormFactory::singleton();

        $classes = Config::inst()->get(
            DataObjectLinkModalExtension::class,
            'classes',
            Config::EXCLUDE_EXTRA_SOURCES
        );
        if (!$classes) {
            $classes = [];
        }
        $sanitizeClasses = [];
        foreach ($classes as $cl => $name) {
            $key = str_replace('\\', '_', $cl);
            $sanitizeClasses[$key] = $name;
        }

        $text = $this->getOwner()->getRequest()->getVar('Text');
        $class = $this->getOwner()->getRequest()->getVar('ClassName');
        $objId = $this->getOwner()->getRequest()->getVar('ObjectID');
        $descr = $this->getOwner()->getRequest()->getVar('Description');
        $targetBlank = $this->getOwner()->getRequest()->getVar('TargetBlank');
        
        return $factory->getForm(
            $this->getOwner(),
            'editorDataObjectLink',
            [
                'RequireLinkText' => isset($showLinkText) || isset($text),
                'AllowedClasses' => $sanitizeClasses,
                'ClassName' => $class ? $class : null,
                'ObjectID' => $objId ? $objId : null,
                'Description' => $descr ? $descr : null,
                'TargetBlank' => $targetBlank ? $targetBlank : null,
            ]
        );
    }
}
