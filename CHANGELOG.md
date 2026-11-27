Personal Data Manager bundle for Contao Open Source CMS
======================

v2.0.0 - Unreleased
- BREAKING : PHP 8.4 & Contao 5.7 minimum
- BREAKING : Contao 5 bundle structure (`config/`, `contao/`, `public/`), services are autowired (legacy service ids kept as aliases)
- BREAKING : The back end module is a route controller (`AbstractBackendController`), the front end module a fragment controller (`AbstractFrontendModuleController`), both rendered with Twig templates
- BREAKING : `mod_personaldatamanager*` and `be_wem_personal_data_manager` templates replaced by `frontend_module/wem_personal_data_manager`, `backend/wem_personal_data_manager` and `personal_data_manager/_email_form` Twig templates
- BREAKING : The access email is a translated Twig template (`personal_data_manager/email`) sent with the Symfony Mailer
- BREAKING : `PersonalDataManagerAction::processAjaxRequest()` returns a `Response`, `PersonalDataManager::export*()` return the path of a temporary zip archive
- BREAKING : `PersonalDataManagerUiFormatter` & `Classes\FileUtil` removed, `tl_settings.wem_pdm_encryption_key` field removed (see README for the encryption key)
- BREAKING : Back end actions require the access to the module instead of the admin role
- Fix : `PersonalDataTrait::find()` endlessly re-running queries returning collections
- Fix : `PersonalDataManager::validateObject()` checking a wrong trait name
- Fix : Access tokens are generated with a cryptographically secure generator
- Fix : Export archives are not written in the web root anymore
- Fix : Error responses do not contain stack traces anymore
- Fix : `Load` & `Save` field callbacks are not shared services anymore
- Feat : Indexes on `tl_wem_personal_data` & `tl_wem_personal_data_access_token`
- Feat : ECS, Rector & PHPStan configurations

v1.3.1 - 2025-12-17
- Fix : `tl_wem_personal_data.value` forced as `string`

v1.3.0 - 2024-11-04
- Feat : PHP 8.2 compatibility
- Feat : `webexmachina/contao-utils` ^2.0 version required

v1.2.0 - 2024-08-22
- Feat : Add a new column "altered" to allow specific values format
- Feat : Arrays can now be stored and marked as serialized
- Feat : Serialized values are properly deserialized when fetching them through Models

v1.1.0 - 2024-08-05
- feat: expand contao-utils compatibility to version 2.0

v1.0.6 - 2024-01-29
- FIXED : Do not try to manage PDM data referencing an unexisting source model

v1.0.5 - 2024-01-24
- FIXED : CSS & JS files for modal were not included in the BE page

v1.0.4 - 2023-08-16
- UPDATED : bundle now requires [webexmachina/contao-utils](https://github.com/Web-Ex-Machina/contao-utils) ^1.0

v1.0.3 - 2023-08-10
- FIXED : If a field is not transmitted to the model's `preSave()` function, it should not be managed by the PDM

v1.0.2 - 2023-06-15
- UPDATED : PHP 8.2 compatibility
- FIXED : undefined variable given as parameter in `buildSingleItemButtons`'s hook has been removed (documentation was not mentionning it)

v1.0.1 - 2023-05-03
- Fix : member toggling in BE could result in data loss

v1.0.0 - 2022-12-14
First release !

- Feat : adding doc for Newsletter override