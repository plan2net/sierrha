# Ṣıẹrrḥa - Site Error Handler

Enhanced error handlers for TYPO3's site error handling system.

## Features

### 404 Not Found Handler

- Displays content from a configurable TYPO3 page or external URL
- Falls back to standard TYPO3 error layout if content is unavailable
- Optimized handling for web resources (CSS, JS, fonts, etc.)
- Bandwidth-saving responses for static resources

### 403 Forbidden Handler

- Redirects unauthenticated users to a configurable login page
- Optional fallback page for users with insufficient permissions
- Configurable return URL parameter with marker support
- Falls back to 404 handler in other cases

## Handlers

### Page Not Found (HTTP Status 404)

Displays content from a page or external URL.

If the resource is unavailable or the content is empty, a message in the standard TYPO3 error layout is shown.

When the requested URL denotes a web resource (eg a CSS file) only a small response is sent to save bandwidth
("Regular expression for resource file extensions", see [Extension Manager Configuration][em]).

The file extensions to be treated by default as web resources:

* css
* eot, ttf, woff, woff2
* gif, ico, jpg, jpeg, png, svg, webp
* js
* json
* xml

### Forbidden (HTTP Status 403)

Redirects to a login URL if access to page without a session is not permitted.

If the user is already logged in, but has no access because of missing group rights he will be optionally redirected to
a fallback page ("Show Content from Page on Missing Permissions", see [Site Configuration][site]).

In any other case a 404 "Not found" error is triggered. TYPO3 will invoke the configured error handler.

## Requirements

* TYPO3 13 LTS, PHP 8.2+
* 404: A page/URL that contains a human-readable "page not found" message
* 403: A URL that performs a login and a redirect to a supplied URL (eg. extension "felogin")
* the web server must be able to reach itself under the configured domain

## Installation

Install via composer

```sh
composer require plan2net/sierrha
```

## Extension Manager Configuration

_Regular Expression for Resource File Extensions_:

This is the default regular expression.

`css|eot|gif|ico|jpe?g|js(?:on)|png|svg|ttf|webp|woff2?|xml`

_Enable Debug Mode_:

In case of configuration errors a detailed error will be shown when in _debug mode_ or if the HTTP request comes from an
IP listed in `$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']`. Otherwise, the error will be passed on to be handled by
TYPO3.

## Site Configuration

### 404 "not found"

On tab "Error Handling" create a new handler.

**HTTP Error Status Code:** "404"
**How to handle Errors:** "PHP Class"

Save the configuration.

**ErrorHandler Class Target (FQCN):** `Plan2net\Sierrha\Error\StatusNotFoundHandler`
**Show Content from Page on Not Found:** TYPO3 page or external URL

### 403 "forbidden"

On tab "Error Handling" create a new handler.

**HTTP Error Status Code:** "403"
**How to handle Errors:** "PHP Class"

Save the configuration.

**ErrorHandler Class Target (FQCN):** `Plan2net\Sierrha\Error\StatusForbiddenHandler`
**Login Page:** TYPO3 page or external URL
**Show Content from Page on Missing Permissions:** TYPO3 page or external URL
**Return Parameter for Login Page URL:** URL query parameter of the login page without leading ? or &

_Note:_ The parameter for the login page used by the extension "felogin" is `return_url=###URL###`.

### URL Markers

The return parameter of the URL supports marker substitution.

Marker | Description
------ | -----------
###URL### |current URL (URL encoded)
###URL_BASE64### | current URL base64 encoded (URL encoded)
###ISO_639-1### | current language as two letter ISO code (ISO 639-1)
###IETF_BCP47### | current language as IETF language tag (IETF BCP 47, RFC 5646/4646/3066/1766) aka "hreflang"

## Caching

Error pages are cached in TYPO3's page cache. For TYPO3 pages (not external URLs), the cache is automatically invalidated when page content changes.

## Changelog

* 0.4.3 TYPO3 CMS 13 and PHP 8.2 compatible release
* 0.4.2 PHP 8.1 compatible release
* 0.4.1 Prevent exception when language not available from request
* 0.4.0
    * Error pages are cached in TYPO3's page cache
    * Set as compatible with v11 LTS
* 0.3.8 Add extension-key to composer.json
* 0.3.7 Prevent 403 handler from getting caught in a loop
* 0.3.6 Prevent 404 handler from getting caught in a loop
* 0.3.5 Don't fetch error page twice
* 0.3.4 Set as compatible with v10 LTS
* 0.3.3
    * Add fallbacks for missing error content
    * Do not throw exceptions in case of configuration errors by default
* 0.3.2 Add eot, ttf and woff/woff2 to the list of web resources
* 0.3.0 Show error page for 404 status; send only a small 404 response for missing web resources
* 0.2.0 Show error page on missing permission for current login
* 0.1.0 Redirect to login page

[em]: #extension-manager-configuration

[site]: #site-configuration
