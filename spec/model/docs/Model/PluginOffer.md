# # PluginOffer

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | ID of the plugin for update purposes, should be a URI specified in the &#x60;Update URI&#x60; header field. | [optional]
**slug** | **string** | Slug of the plugin. | [optional]
**plugin** | **string** | The file path of the plugin. | [optional]
**new_version** | **string** | The new version of the plugin available for update. | [optional]
**url** | **string** | The URL for details of the plugin. | [optional]
**package** | **string** | Optional. The update ZIP for the plugin. | [optional]
**icons** | **array<string,string>** | Optional. Array of plugin icons with different resolutions. | [optional]
**banners** | **array<string,string>** | Optional. Array of plugin banners. | [optional]
**banners_rtl** | **string[]** | Optional. Array of plugin RTL banners. | [optional]
**requires** | **string** | Optional. The minimum version of WordPress required for the plugin. | [optional]
**tested** | **string** | Optional. The version of WordPress the plugin is tested against. | [optional]
**requires_php** | **string** | Optional. The minimum version of PHP required for the plugin. | [optional]
**requires_plugins** | **string[]** | Optional. List of required plugins for this plugin. | [optional]
**compatibility** | **string[]** | Optional. List of compatibility data for this plugin. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
