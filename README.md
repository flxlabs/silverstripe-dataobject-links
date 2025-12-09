# DataObject Links

**Add links to DataObjects from within the TinyMCE editor**

## Introduction

Using this module you can link to DataObjects from within TinyMCE.

## Requirements

- Silverstripe CMS ^6
- silverstripe/htmleditor-tinymce ^1

## Setup

You can either add this module to your composer file using

```sh
composer require flxlabs/silverstripe-dataobject-links
```

or download the git repository and add a folder called `dataobject-links` to the top level
of your project and drop the code in there.

## Configure

In your settings `.yml` file put a section with

```yaml
FLxLabs\DataObjectLink\DataObjectLinkModalExtension:
  classes:
    Team:
      name: Team
    Player: Player
      name: Player
      sort: Title
```

Where `classes` is a map from ClassName to display name that is used when selecting the DataObject.

Make sure to run `/dev/build?flush` to load in your config changes.

## Usage

In TinyMCE's `Insert link` menu there will be a new entry `Link to an Object` at the very top. Use that to link to an Object of one of the classes that is listed in your config.

When generating the link this module will call the `Link` method on a DataObject. Override it to provide a link to your DataObject.

```php
// This code doesn't actually work, it's just an example
public function Link() {
  return DataObject::get()->first()->Link() . $this->ID;
}
```
