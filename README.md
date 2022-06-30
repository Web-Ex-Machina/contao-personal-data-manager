Personal Data Manager bundle for Contao Open Source CMS
======================

The purpose of this project is to allow a better handling of personal data in Contao CMS.

Functionnalities
-------------------
 
 * Store personal data
 * Retrieve personal data

System requirements
-------------------

 * Contao 4.13

Installation
------------

Clone the extension from Packagist (Contao Manager)


Configuration
-------------

First, you will need to define an encryption key.
Then, models (and their associated DCA) need to be adjusted.

### Encryption key

As this bundle uses [`plenta/contao-encryption`][4], an encryption key needs to be set. When the bundle is loaded, it checks wether the encryption key `plenta_contao_encryption.encryption_key` is set or not.

If not, it will look for ours, named `wem_pdm_encryption_key`, in the `/system/config/localconfig.php` file. And apply it to the `plenta_contao_encryption.encryption_key` container parameter.

### Model

Use the `WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait` trait in your Model and define the mandatories static properties.

Eg :

```php
<?php 

use WEM\PersonalDataManagerBundle\Model\Traits\PersonalDataTrait as PDMTrait;

class MyModel
{
    use PDMTrait;
    /** @var array Fields to be managed by the Personal Data Manager */
    protected static $personalDataFieldsNames = ['myField'];
    /** @var array Default values for fields to be managed by the Personal Data Manager */
    protected static $personalDataFieldsDefaultValues = ['myField' => 'managed_by_pdm'];
    /** @var array Values for fields to be managed by the Personal Data Manager when anonymized */
    protected static $personalDataFieldsAnonymizedValues = ['myField' => 'Anonymized'];
    /** @var string Field to be used as pid by the Personal Data Manager */
    protected static $personalDataPidField = 'id';
    /** @var string Field to be used as email by the Personal Data Manager */
    protected static $personalDataEmailField = 'email';
    /** @var string ptable to be used by the Personal Data Manager */
    protected static $personalDataPtable = 'tl_my_table';

```

This way, when saving a `MyModel` object, the real value of `MyField` will not be stored in the `MyModel`'s table but in the Personal Data Manager one, and be associated with the corresponding `MyModel`'s id and with the defined `$personalDataPtable`. `MyField` 'stored value in `tl_my_table` will be the one defined in `MyMode::personalDataFieldsDefaultValues` ('managed_by_pdm' in our example).

And when retrieving the `MyModel` object from the database, the Personal Data Manager will automatically fetch the associated personal data's and fill the `MyModel`'s object accordingly.

### Custom Table Driver

This bundles provides a Trait to allow grouping in back-end list to work. To make it work, you will need to define a custom DCA Table Driver (if you already have a custom one, you will still have to create another one, as the trait assume it works on a DCA with a model configured to use the personal data manager).

```php
<?php

declare(strict_types=1);

namespace Your\Namespace;

use WEM\PersonalDataManagerBundle\Dca\Driver\PDMDCTableTrait;

class DC_Table extends \Contao\DC_Table
{
    use PDMDCTableTrait;
}
```

### DCA

In your DCA, your need to add the following callbacks & driver:

```php
use Path\To\Your\Custom\Dca\Driver\DC_Table_Custom;

$GLOBALS['TL_DCA']['tl_my_table'] = [
    'config'=>[
        // ...
        'dataContainer' => DC_Table_Custom::class,
        'ondelete_callback' => [['wem.personal_data_manager.dca.config.callback.delete', '__invoke']],
        'onshow_callback' => [['wem.personal_data_manager.dca.config.callback.show', '__invoke']],
    ],
    'list'=>[
        'label' => [
            // ...
            'label_callback' => ['wem.personal_data_manager.dca.listing.callback.list_label_label_for_list', '__invoke'],
        ],
    ],
    'fields'=>[
        'myField'=>[
            // ...
            'load_callback' => [['wem.personal_data_manager.dca.field.callback.load', '__invoke']],
            'save_callback' => [['wem.personal_data_manager.dca.field.callback.save', '__invoke']],
        ]
    ]
]

```

This way, editing your records in back-end will work with the same as with the model.

:warning: If the data you are working on can be edited throught the contao's `Personal data` front end module, you will need to extends the `load` & `save` callbacks to precise on which table & field the front end callback should work !

**PHP callback**
```php
<?php

declare(strict_types=1);

namespace Your\Namespace\Dca\Field\Callback;

use WEM\PersonalDataManagerBundle\Dca\Field\Callback\Load as PdmCallback; // or Save

class Load
{
    /** @var WEM\PersonalDataManagerBundle\Dca\Field\Callback\Load */
    private $pdmCallback;
    /** @var string */
    private $frontendField;
    /** @var string */
    private $table;

    public function __construct(
        PdmCallback $pdmCallback,
        string $frontendField,
        string $table
    ) {
        $this->pdmCallback = $pdmCallback;
        $this->frontendField = $frontendField;
        $this->table = $table;

        $this->pdmCallback->setFrontendField($this->frontendField)->setTable($this->table);
    }

    public function __invoke()
    {
        return $this->pdmCallback->__invoke(...\func_get_args());
    }
}
```

**YAML config file** (like `your/bundle/src/Resources/contao/config/services.yml`)
```yaml
services:
    your.bundle.dca.field.callback.load.tl_my_table.my_field:
        class: Your\Namespace\Dca\Field\Callback\Load
        arguments:
            $pdmCallback: '@wem.personal_data_manager.dca.field.callback.load'
            $frontendField: 'myField'
            $table: 'tl_my_table'
        public: true
```

**DCA file**
```php
$GLOBALS['TL_DCA']['tl_my_table']['fields']['myField']['load_callback'][] = ['your.bundle.dca.field.callback.load.tl_my_table.my_field', '__invoke'];
```

Usage
-----

Both a back-end entry and a front-end module are provided to allow admin and users to show, export or anonymize their personal data.

### Back-end

The user can enter an email address and all associated personal data are displayed. From there, the user can show, export or anonymize them.

### Front-end

The front-end module needs to be registered in your theme.

The user has to enter an email address in the displayed form. An email containing a link to the current page with a token will be sent to the email address filled in the form. The token is valid for 5 minutes upon its creation. By clicking on the link provided in the email, the user will be redirected to the page where the front-end module lies, with the token as a GET parameter. From there, the user can show, export or anonymize their personal data.

Each action from the user make its token valid for 5 more minutes.

Hooks
-----

Multiple hooks are available to customize the bundle

### Personal Data List

Hook | Return Value | Description
--- | --- | ---
`renderListButtons` | `string` | *To be completed*
`renderSingleItem` | `string` | *To be completed*
`renderSingleItemHeader` | `string` | *To be completed*
`renderSingleItemTitle` | `string` | *To be completed*
`renderSingleItemButtons` | `string` | *To be completed*
`renderSingleItemBody` | `string` | *To be completed*
`renderSingleItemBodyOriginalModel` | `string` | *To be completed*
`renderSingleItemBodyOriginalModelSingle` | `string` | *To be completed*
`renderSingleItemBodyOriginalModelSingleFieldLabel` | `string` | *To be completed*
`renderSingleItemBodyOriginalModelSingleFieldValue` | `string` | *To be completed*
`renderSingleItemBodyPersonalData` | `string` | *To be completed*
`renderSingleItemBodyPersonalDataSingle` | `string` | *To be completed*
`renderSingleItemBodyPersonalDataSingleFieldLabel` | `string` | *To be completed*
`renderSingleItemBodyPersonalDataSingleFieldValue` | `string` | *To be completed*
`renderSingleItemBodyPersonalDataSingleButtons` | `string` | *To be completed*
`getHrefByPidAndPtableAndEmail` | `string` | URL to show the current item

### CSV Exporter

Hook | Return value | Description
--- | --- | ---
`formatHeaderForCsvExport` | `array` | Header for CSV exports
`formatSinglePersonalDataForCsvExport` | `array` | Data for each single Personal Data item

Documentation
-------------

 * [Change log][1]
 * [Git repository][2]

License
-------

This extension is licensed under the terms of the Apache License 2.0. The full license text is
available in the main folder.


Getting support
---------------

Visit the [support page][3] to submit an issue or just get in touch :)


Installing from Git
-------------------

You can get the extension with this repository URL : [Github][2]

[1]: CHANGELOG.md
[2]: https://github.com/webexmachina/personal-data-manager
[3]: https://www.webexmachina.fr/
[4]: https://github.com/plenta/contao-encryption