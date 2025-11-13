<?php

namespace FLxLabs\DataObjectLink;

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
		'EditorDataObjectLink',
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
	public function EditorDataObjectLink()
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
		foreach ($classes as $cl => $data) {
			$key = str_replace('\\', '_', $cl ?? '');
			$sanitizeClasses[$key] = $data['name'];
		}

		$text = $this->getOwner()->getRequest()->getVar('Text');
		$class = $this->getOwner()->getRequest()->getVar('ClassName');
		$objId = $this->getOwner()->getRequest()->getVar('ObjectID');
		$dependantClass = $this->getOwner()->getRequest()->getVar('DependantClassName');
		$depdendantObjId = $this->getOwner()->getRequest()->getVar('DependantObjectID');
		$descr = $this->getOwner()->getRequest()->getVar('Description');
		$targetBlank = $this->getOwner()->getRequest()->getVar('TargetBlank');

		return $factory->getForm(
			$this->getOwner(),
			'EditorDataObjectLink',
			[
				'RequireLinkText' => isset($showLinkText) || isset($text),
				'AllowedClasses' => $sanitizeClasses,
				'ClassName' => $class ? $class : null,
				'ObjectID' => $objId ? $objId : null,
				'DependantClassName' => $dependantClass ? $dependantClass : null,
				'DependantObjectID' => $depdendantObjId ? $depdendantObjId : null,
				'Description' => $descr ? $descr : null,
				'TargetBlank' => $targetBlank ? $targetBlank : null,
			]
		);
	}
}
