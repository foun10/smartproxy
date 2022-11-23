# Smartproxy module for OXID

this module can be used if the Smartproxy HTML cache from ScaleCommerce is available and set up.
The module handles some caching logics and replaces (session) placeholders in cached HTML.

## Smartproxy default info

* Smartproxy will cache all requests to a SEO url
* all POST requests won't be cached
* GET parameters will affect the hashed URL and changing results in a new cache file

## Environment

Not every request for a cacheable call will be the same. e.g. if you have different currencies set up this information is stored in session and won't affect the SEO url.
So there is a need to make those information available on Smartproxy to have a different caching if these environment variables are set.

It is possible to set up a cookie thats value is used on Smartproxy to determine a different cache hash.

The cookie key is ``smartproxy_env_key`` and it will be set up on the first call that goes through Smartproxy.
Additionally if this cookie is not set there is some JS logics that will get the value immediately (see Javascript logics section for further information).

### Set up your environment

On default the module will create a hash for the environment key from the following information:

* sorting: if sorting on alist is set up
* loggedIn: if user is logged in
* productsPerPage: amount of products shown on lists
* currency: chosen currency
* language: chosen language
* shop: current shop id

All values will be put together and hashed and set as the cookie value. On changing of some of these variables the cookie value will be set up again.

_Please note that the request that affects one of these variables needs to be non cacheable - e.g. by POST request or specific parameters on url (see **How to set up if call is cacheable?**)_

### Adjustments to this logic

You have the possibility to make adjustments to this logic. Maybe you do not need the info if the user is logged in or not or you have another variable that should affect the cookie value.

You can simply extend the class ``foun10\SmartProxy\Environment\EnvironmentKeyLogics`` and overwrite the function ``customizeEnvironmentValues(array $values)``
The $values parameter will have the default keys set up and you can simply remove/adjust/add those.
e.g. to remove the loggedIn state you can simply ``unset($values['loggedIn']);`` 

### Mandatory cookies

``smartproxy_env_key`` is defined as mandatory cookie so there will be a JS call after page load if this cookie is not set.
This makes sure that on the second request the environment is correctly set up.

If you need additional mandatory cookies you can set up those by extending the function ``foun10\SmartProxy\Core\SmartProxy::getMandatoryCookies()``

If a mandatory cookie is needed but not set the module will call the controller ``foun10SmartProxyEnvironmentSetter`` and set up the cookies.
If you adjusted the mandatory cookies you need to extend the function ``foun10\SmartProxy\Core\SmartProxy::setAdditionalCookies()``, too.
For info how to have a look on ``foun10\SmartProxy\Core\SmartProxy::setCookies()``

### Session parameter and replacements

The stoken parameter on forms will be replaced with an placeholder so the full HTML is cacheable.
e.g. ``<input type="hidden" name="stoken" value="###smartproxy_stoken###" />`` 
_This replacement will only happen if the call is detected as cacheable._

If the cookie ``smartproxy_stoken`` is already set up, Smartproxy itself will replace the placeholder before sending the HTML to the browser.

If not or if it is the first request there is some JS logic (see _JS logics_) that will do the replacement in every form input.

You can set up those replacements by extending the ``foun10\SmartProxy\Core\SmartProxy::getInputValueReplacements()`` function.

## Caching of requests without SEO URL

On Smartproxy default setup all requests to a SEO url will be cached. But you might want to cache also requests to index.php

For this Scale Commerce needs to adjust the Smartproxy logics to make calls to index.php also cacheable.
To have the possibility to decide to cache or not to cache there is a check implemented in this module.

### Info

Scale Commerce has two options how the cache can be applied: "blacklist" and "whitelist".

That results in the way Smartproxy interprets the HTTP response header ``x-sc-sp-cache``

Scale Commerce can setup the caching on these two ways:
* Only cache request if ``x-sc-sp-cache: yes`` is set (whitelist), all other requests won't be cached
* Do not cache request if ``x-sc-sp-cache: no`` is set (blacklist), all other requests will be cached

The module will always send the HTTP header ``x-sc-sp-cache``. You can set up the logics either if its value ``yes`` or ``no``.

**Attention:** The module only sets this HTTP header if the controller logics is using 

```
$header = oxNew(\OxidEsales\Eshop\Core\Header::class);
$header->sendHeader();
```
e.g. if there will be some JSON data returned

or the output will be fully process by render() function (the module extends ``ShopControl::sendAdditionalHeaders()``).

### How to set up if call is cacheable?

1. **By module settings:**
There are some module settings that will be used to determine if the call is cacheable or not.
    - FOUN10_SMART_PROXY_CACHEABLE_CONTROLLERS: You can define an array of controller (action) classnames that will set ``x-sc-sp-cache: yes`` all other controllers will set ``x-sc-sp-cache: no``
    - FOUN10_SMART_PROXY_NON_CACHEABLE_PARAMETERS: You can define an array of request parameters that will set ``x-sc-sp-cache: no``
    
2. **By controller logics:**
The module will check if there is a function ``isSmartProxyCachable()`` for the current controller. Return true is the call is cacheable and false if not.

More info you will find within the function ``foun10\SmartProxy\Core\SmartProxy::isCacheableCall()``

### Template

If you need to make adjustments to the HTML you can use the following condition to check if the call is cacheable:

```
[{if $oViewConf|method_exists:'isSmartProxyActive' && $oViewConf->isSmartProxyActive()}]
```

## JS logics

All the JS logics can be found in ``modules/foun10/SmartProxy/out/js/foun10SmartProxy.js``

### Replacements

There are logics to replace defined placeholders within form inputs with cookie values. If the values are not available they will be fetched from the application.

### Fetch from app server

A call to the application to set up cookies will be done if one of the following applies:
* a mandatory cookie is missing
* an url parameter that is a "refresh" prameter is set up
* there are needed input replacements found but no cookie value is set up

### Events

There are events that will be triggered for some states. You can listen to these events to add some custom logics.

* ``foun10SmartProxySetUpEnvironment``: will be triggered initially and makes sure all mandatory cookies and all cookie values for replacements are present
* ``foun10SmartProxyReplace``: will be triggered if cookie value is available and triggers replacement
* ``foun10SmartProxyFetchDataComplete``: will be triggered if fetch from remote is completed
* ``foun10SmartProxyFetchData``: will be triggered if fetch from remote is needed

#### Example:

You have a <div> on your HTML where the basket amount is displayed on cacheable pages it is just empty: ``<div class="cart-amount"></div>``
To add the correct basket amoun can use (requirement is that you have set up the cookie logics for basket amount):

```
window.addEventListener('foun10SmartProxyReplace', function (e) {
    let basketAmount = e.detail.data.smartproxy_basket_amount || 0;

    if (basketAmount !== 'empty' && basketAmount > 0) {
        document.querySelector('.cart-amount').innerHTML = basketAmount;
    }
}, false);
```

### XHR check

It could be that there will be some XHR requests that gets triggered before any replacement is done. To prevent sending requests with placeholder there is an extension to the ``XMLHttpRequest.prototype`` to look for defined placeholders.
If placeholders are found the request gets paused until the replacement data is fetched from the application. Then the replacement will be done and the XHR request will be sent.

### Debug

There is some debug information in browser console to see what is happening. You can enable/disable that with module option ``FOUN10_SMART_PROXY_JS_DEBUG``

## Cache Tags

Smartproxy comes with the ability to assign cache tags for the cached HTML. This module already handles some of these:

* cl=xyc: xyz will be added as cache tag: e.g. ``xyz``
* cl=details: (Parent-) Product ID will be added as cache tag: e.g. ``details details-123456``
* cl=alist: Category ID will be added as cache tag: e.g. ``alist alist-123456``
* cl=content: Content ID will be added as cache tag: e.g. ``content content-123456``

### Adjustments to this logic

see logics in ``modules/foun10/SmartProxy/Core/SmartProxyCacheTags.php``. You can either extend the given functions or add the function ``getSmartproxyCacheTags()`` on your (custom) controller. ``getSmartproxyCacheTags()`` needs to return an array with the cache tags of your choice.  

## Command for cache invalidation

There are OXID console commands to clear the smartproxy cache.

Both commands will trigger a rundeck job so make sure the config values for ``FOUN10_SMART_PROXY_RUNDECK_JOB_HTML_CLEAR`` and ``FOUN10_SMART_PROXY_RUNDECK_JOB_TAG_CLEAR`` are set up and available on your rundeck instance.

On default there should already be these job ids:
* ``FOUN10_SMART_PROXY_RUNDECK_JOB_HTML_CLEAR``: global-sc-rundeck-jobs-smartproxy-delete-html-cache
* ``FOUN10_SMART_PROXY_RUNDECK_JOB_TAG_CLEAR``: Smartproxy_Tag_Cache_Clear_by_Filename

``foun10:smartproxy:cache clear-html``

Use this to clear the complete smartproxy HTML cache. e.g. after deployment.

``foun10:smartproxy:cache clear-tag --tag=XXX``

Use this to clear the smartproxy HTML cache for given cache tag.

## Known issues / common problems

### Tracking

All tracking that is implemented on cacheable pages **and** has some user/session defined data should be adjusted.
_Hint: Normally these trackings are commonly on the thankyou page that will not be cached by default._

#### e.g. current basket tracking on every page

There is some logic to get tracking data from remote on cacheable pages. You can use this logic to get the data needed or set up cookies and get data from there.

Example for tracking data by remote:

before:
```
_trboq.push([
    "currentBasket",
    {
        value: 100,
        product_ids: [
            "abc",
            "def"
        ]
    }
]);
```

after:
```
[{if $oViewConf->isSmartProxyActive()}]
    if (foun10SmartProxy.getCookie('smartproxy_basket_amount') > 0) {
        foun10SmartProxy.getTrackingData('trbo_current_basket').then(function(data) {
            _trboq.push([
                "currentBasket",
                data
            ]);
        });
    }
[{else}]
    _trboq.push([
        "currentBasket",
        {
            value: 100,
            product_ids: [
                "abc",
                "def"
            ]
        }
    ]);
[{/if}]
```

### Dynamic content

In OXID you can set up dynamic content that should not be cached with [{oxid_include_dynamic file="..."}] Smarty tag. 
Smartproxy (for now) does not have the ability to load these contents dynamically from app server like it is possible with Server Side Includes.

A common example would be the minibasket with its values.

**You need to make sure that those information will be loaded (async) with Javascript**