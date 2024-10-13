# # UpdateOffer

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**response** | **string** | The response type (e.g., upgrade, autoupdate) | [optional]
**download** | **string** | URL to download the update | [optional]
**locale** | **string** | The locale for the update | [optional]
**packages** | [**\OpenWPAPI\Model\UpdateOfferPackages**](UpdateOfferPackages.md) |  | [optional]
**current** | **string** | The current version of the software | [optional]
**version** | **string** | The new version available for update | [optional]
**php_version** | **string** | Required PHP version | [optional]
**mysql_version** | **string** | Required MySQL version | [optional]
**new_bundled** | **string** | New bundled version | [optional]
**partial_version** | **string** | The partial version if applicable | [optional]
**new_files** | **bool** | Whether new files are included in the update | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
