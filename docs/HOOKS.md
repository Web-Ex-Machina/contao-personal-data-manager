Hooks
=====

Here you can find all available hooks and their documentation.

:warning: Those hooks aren't registered in `$GLOBALS['TL_HOOKS']` but in `$GLOBALS['WEM_HOOKS']`.

List
----

### Personal Data List


![Personal Data Manager UI annotated for hooks](ui.jpg)


Hook | Return Value | Description
--- | --- | ---
`renderListButtons` | `string` | Called after the action buttons for the whole list have been generated.
`renderSingleItem` | `string` | Called after a whole item have been generated.
`renderSingleItemHeader` | `string` | Called after the item's header have been generated.
`renderSingleItemTitle` | `string` | Called after an item's header's title have been generated.
`renderSingleItemButtons` | `string` | Called after an item's header's buttons have been generated.
`renderSingleItemBody` | `string` | Called after an item's body have been generated.
`renderSingleItemBodyOriginalModel` | `string` | Called after an item's whole original model have been generated.
`renderSingleItemBodyOriginalModelSingle` | `string` | Called after an item's original model's single row have been generated
`renderSingleItemBodyOriginalModelSingleFieldLabel` | `string` | Called after an item's original model's single row field label have been generated
`renderSingleItemBodyOriginalModelSingleFieldValue` | `string` | Called after an item's original model's single row field value have been generated
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

Details
-------

### renderListButtons

Called after the action buttons for the whole list have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$email | `string` | The email address linked to the personal data
$nbRows | `int` | Number of items in the list
$buffer | `string` | The generated HTML code

```php
public function renderListButtons(string $email, int $nbRows, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItem

Called after a whole item have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$email | `string` | The email address linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItem(int $pid, string $ptable, string $email, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemHeader

Called after an item's header have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemHeader(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemTitle

Called after an item's header's title have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemTitle(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemButtons

Called after an item's header's buttons have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemButtons(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBody

Called after an item's body have been generated.

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBody(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyOriginalModel

Called after an item's whole original model have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyOriginalModel(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyOriginalModelSingle

Called after an item's original model's single row have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$field | `string` | The field linked to the personal data
$value | `mixed` | The value linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyOriginalModelSingle(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyOriginalModelSingleFieldLabel

Called after an item's original model's single row field label have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$field | `string` | The field linked to the personal data
$value | `mixed` | The value linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyOriginalModelSingleFieldLabel(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyOriginalModelSingleFieldValue

Called after an item's original model's single row field value have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$field | `string` | The field linked to the personal data
$value | `mixed` | The value linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyOriginalModelSingleFieldValue(int $pid, string $ptable, string $field, $value, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyPersonalData

Called after an item's whole personal data list have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyPersonalData(int $pid, string $ptable, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyPersonalDataSingle

Called after an item's personal data list row have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalData | `WEM\PersonalDataManagerBundle\Model\PersonalData` | The personal data row linked to the item
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyPersonalDataSingle(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyPersonalDataSingleFieldLabel

Called after an item's personal data list row's field label have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalData | `WEM\PersonalDataManagerBundle\Model\PersonalData` | The personal data row linked to the item
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyPersonalDataSingleFieldLabel(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyPersonalDataSingleFieldValue

Called after an item's personal data list row's field value have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalData | `WEM\PersonalDataManagerBundle\Model\PersonalData` | The personal data row linked to the item
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyPersonalDataSingleFieldValue(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```

### renderSingleItemBodyPersonalDataSingleButtons

Called after an item's personal data list row's field buttons have been generated

**Return value** : `string`

**Arguments**:
Name | Type | Description
--- | --- | ---
$pid | `int` | The pid linked to the personal data
$ptable | `string` | The ptable linked to the personal data
$personalData | `WEM\PersonalDataManagerBundle\Model\PersonalData` | The personal data row linked to the item
$personalDatas | `array` | All personal data linked to the item
$originalModel | `Contao\Model` | The original model
$buffer | `string` | The generated HTML code

```php
protected function renderSingleItemBodyPersonalDataSingleButtons(int $pid, string $ptable, PersonalData $personalData, array $personalDatas, Model $originalModel, string $buffer): string
{
	return $buffer;
}
```