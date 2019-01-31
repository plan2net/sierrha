# Ṣıẹrrḥa - Site Error Handler

A set of error handlers that extends TYPO3's default site error handling (work in progress).

For now only one handler is available:

* _403 "forbidden"_: redirects to a login URL if access to page without session is not permitted

## Requirements

* TYPO3 9 LTS
* A URL that performs a login and a redirect to a supplied URL (eg. plugin "felogin")

## Installation

Add via composer.json: 

```
"require": {
  "plan2net/sierrha": "*"
}
```

Install and activate the extension in the Extension manager.

## Extension Manager Configuration

Tick the checkbox to enable the _debug mode_:

In case of configuration errors a detailed error will be shown when in _debug mode_ or
if the HTTP request comes from an IP listed in `$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']`.
Otherwise the error will be passed on to be handled by TYPO3.

## Site Configuration

On tab "Error Handling" create a new handler.

**HTTP Error Status Code:** "403"  
**How to handle Errors:** "PHP Class"

Save the configuration.

**ErrorHandler Class Target (FQCN):** "Plan2net\Sierrha\Error\StatusForbiddenHandler"  
**Login Page:** TYPO3 page or external URL  
**Return Parameter for Login Page URL:** URL query parameter of the login page without leading ? or &

The parameter used by the extension "felogin" is `return_url=###URL###`.

### URL Markers

The return parameter of the URL supports marker substitution.  

Marker | Description
------ | -----------
###URL### |current URL (URL encoded)
###URL_BASE64### | current URL base64 encoded (URL encoded)
###ISO_639-1### | current language as two letter ISO code (ISO 639-1)
###IETF_BCP47### | current language as IETF language tag (IETF BCP 47, RFC 5646, RFC 4646, RFC 3066, RFC 1766)
