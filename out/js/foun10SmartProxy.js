var foun10SmartProxy = {
    debug: window.foun10SmartProxyDebug || false,
    replacements: window.foun10SmartProxyReplacements || [
        'smartproxy_stoken',
        'smartproxy_sid'
    ],
    mandatoryCookies: window.foun10SmartProxyMandatoryCookies || [
        'smartproxy_env_key'
    ],
    refreshUrlParameter: window.foun10SmartProxyRefreshUrlParameter || [],
    xhrEventsRegistered: false,
    init: function() {
        var self = this;

        self.registerEvents();
        self.registerXHRListener();

        window.dispatchEvent(new Event('foun10SmartProxySetUpEnvironment'));

        if (self.checkUrlParameterForRefresh()) {
            window.dispatchEvent(new Event('foun10SmartProxyFetchData'));
        }
    },
    /**
     * Fetch cookies from remote
     */
    fetchData: async function() {
        var self = this;

        return fetch('/index.php?cl=foun10SmartProxyEnvironmentSetter', {
            method: 'GET',
            cache: 'no-cache',
            headers: {
                'Content-Type': 'application/json'
            },
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            self.debugOut('smartproxy: ... fetched from remote');

            window.dispatchEvent(new CustomEvent('foun10SmartProxyFetchDataComplete', {
                detail: {
                    data: data
                }
            }));
        });
    },
    getPlaceholderValue: function(value) {
        return '###' + value + '###';
    },
    /**
     * Register XHR listeners
     */
    registerXHRListener: function() {
        var self = this;

        if (self.xhrEventsRegistered) {
            return;
        }

        /**
         *  Check if there is any placeholder in request data that should be replaced before
         */
        var send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = async function(data) {
            var fetchNeeded = false;
            var xhrArguments = arguments[0];
            var parameterString = decodeURIComponent(arguments[0]);

            // Check if fetch data is needed
            self.replacements.forEach(function(value) {
                var cookieValue = self.getCookie(value);

                if (!cookieValue) {
                    fetchNeeded = true;
                }
            });

            if (fetchNeeded) {
                self.debugOut('fetch from remote because placeholder cookie is missing but needed...');

                // Use await to make sure data will be replaced
                await self.fetchData();
            }

            self.replacements.forEach(function(value) {
                var cookieValue = self.getCookie(value);
                var placeHolderValue = self.getPlaceholderValue(value);

                if (parameterString.indexOf(placeHolderValue) !== -1 && cookieValue) {
                    var regex = new RegExp(encodeURIComponent(placeHolderValue), 'g');
                    xhrArguments = xhrArguments.replace(regex, cookieValue);
                }
            });

            arguments[0] = xhrArguments;
            send.call(this, data);
        };

        /**
         *  Check if there needs to be a replace after XHR again - maybe some html with placeholders got injected
         */
        var origOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(data, url, async) {
            this.addEventListener('load', function() {
                self.debugOut('replacing after xhr?');

                setTimeout(function() {
                    if (self.isReplaceNeeded()) {
                        window.dispatchEvent(new Event('foun10SmartProxySetUpEnvironment'));
                    }
                }, 500);
            });
            origOpen.apply(this, arguments);
        };

        self.xhrEventsRegistered = true;
    },
    registerEvents: function() {
        var self = this;

        self.debugOut('registring events ...');

        window.addEventListener('foun10SmartProxySetUpEnvironment', function (e) {
            self.setSmartProxyEnvironment();
        }, false);

        window.addEventListener('foun10SmartProxyReplace', function (e) {
            self.doPlaceHolderReplacing(e.detail.data);
        }, false);

        window.addEventListener('foun10SmartProxyFetchDataComplete', function (e) {
            if (self.isReplaceNeeded()) {
                window.dispatchEvent(new CustomEvent('foun10SmartProxyReplace', {
                    detail: {
                        data: e.detail.data
                    }
                }));
            }
        }, false);

        window.addEventListener('foun10SmartProxyFetchData', function (e) {
            self.debugOut('fetch from remote...');
            self.fetchData();
        }, false);

        self.debugOut('... events registered');
    },
    /**
     * Checks if there is any placeholder in input fields that should be replaced
     */
    isReplaceNeeded: function() {
        var self = this;
        var returnValue = false;

        self.replacements.forEach(function(value) {
            var inputsToCheck = document.querySelectorAll('input');

            inputsToCheck.forEach(function(input) {
                var placeHolderValue = self.getPlaceholderValue(value);

                if (input.value === placeHolderValue) {
                    self.debugOut('found replace needed: ' + input.value);
                    returnValue = true;
                }
            });
        });

        self.debugOut('replacing? ' + returnValue);

        return returnValue;
    },
    setSmartProxyEnvironment: function() {
        // Set up environment for smartproxy usage
        var self = this;
        
        var fetchFromRemoteCountReplace = 0;
        var fetchFromRemoteCountMandatory = 0;
        var cookieData = {};

        // Check if needed replacement value is already in cookie
        self.replacements.forEach(function(value) {
            var cookieValue = self.getCookie(value);

            if (!cookieValue && cookieValue !== 'empty') {
                fetchFromRemoteCountReplace = fetchFromRemoteCountReplace + 1;
            } else {
                cookieData[value] = cookieValue;
            }
        });

        if (fetchFromRemoteCountReplace === 0) {
            self.debugOut('found replacements in cookies');
        }

        // Check if needed mandatory value is already in cookie
        self.mandatoryCookies.forEach(function(name) {
            var cookieValue = self.getCookie(name);

            if (!cookieValue) {
                fetchFromRemoteCountMandatory = fetchFromRemoteCountMandatory + 1;
            } else {
                cookieData[name] = cookieValue;
            }
        });

        if (fetchFromRemoteCountMandatory === 0) {
            self.debugOut('found mandatories in cookies');
        }

        var fetchFromRemoteCount = fetchFromRemoteCountReplace + fetchFromRemoteCountMandatory;

        if (fetchFromRemoteCount > 0) {
            // Fetch data from remote and trigger setcookie by that
            window.dispatchEvent(new Event('foun10SmartProxyFetchData'));
        } else {
            // All data was in cookie data, so replace from there
            window.dispatchEvent(new CustomEvent('foun10SmartProxyReplace', {
                detail: {
                    data: cookieData
                }
            }));
        }
    },
    doPlaceHolderReplacing: function(data) {
        // Replace input placeholders with correct data
        var self = this;

        self.debugOut('replacing placeholders ...');

        self.replacements.forEach(function(value) {
            var inputsToCheck = document.querySelectorAll('input');
            var placeHolderValue = self.getPlaceholderValue(value);

            inputsToCheck.forEach(function(input) {
                if (input.value === placeHolderValue) {
                    if (data[value] === 'empty') {
                        input.value = '';
                    } else if (typeof data[value] === 'undefined') {
                        // Check cookie if data is not set
                        var cookieValue = self.getCookie(value);

                        if (cookieValue === 'empty') {
                            cookieValue = '';
                        }

                        input.value = cookieValue;
                    } else {
                        input.value = data[value];
                    }
                }
            });
        });

        self.debugOut('... replaced placeholders');
    },
    getCookie: function(name) {
        // Get cookie value by name
        var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return value ? value[2] : null;
    },
    debugOut: function(message) {
        var self = this;

        if (self.debug) {
            console.log('smartproxy: ' + message);
        }
    },
    checkUrlParameterForRefresh: function() {
        // Check if there are any url parameter that need a cookie refresh (e.g. env_key refresh)
        var self = this;

        self.debugOut('checking for refresh by url parameter ...');

        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var refreshCookies = false;

        // Check if any refresh parameter is set
        self.refreshUrlParameter.forEach(function(parameter) {
            var keyvalue = parameter.split('=');
            var parameterToCheck = urlParams.get(keyvalue[0]);

            if (parameterToCheck !== null && typeof keyvalue[1] === 'undefined' || parameterToCheck === keyvalue[1]) {
                refreshCookies = true;
            }
        });

        self.debugOut('... refresh? ' + refreshCookies);

        return refreshCookies;
    },
    getTrackingData: async function(identifier) {
        // Call this function if any tracking is in need of dynamic tracking data on cached pages
        var self = this;

        self.debugOut('getting tracking data for: ' + identifier);

        return await fetch('/index.php?cl=foun10SmartProxyTrackingData&identifier=' + identifier, {
            method: 'GET',
            cache: 'no-cache',
            headers: {
                'Content-Type': 'application/json'
            },
        }).then(function(response) {
            self.debugOut('fetched tracking data for: ' + identifier);
            return response.json();
        });
    }
};

// Do this first because there might be some xhr request before foun10SmartProxy.init() is called
foun10SmartProxy.registerXHRListener();

// Init after HTML is fully parsed
document.addEventListener("DOMContentLoaded", function(){
    foun10SmartProxy.init();
});
