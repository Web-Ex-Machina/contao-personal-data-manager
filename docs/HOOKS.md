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