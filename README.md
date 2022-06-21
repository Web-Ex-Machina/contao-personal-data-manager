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

Models (and their associated DCA) need to be adjusted.

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