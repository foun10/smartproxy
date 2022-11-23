[{if $oViewConf->isSmartProxyActive()}]
    <script type="text/javascript">
        window.foun10SmartProxyReplacements = window.foun10SmartProxyReplacements || [{$oViewConf->getInputValueReplacements()|@json_encode}];
        window.foun10SmartProxyMandatoryCookies = window.foun10SmartProxyMandatoryCookies || [{$oViewConf->getMandatoryCookies()|@json_encode}];
        window.foun10SmartProxyRefreshUrlParameter = window.foun10SmartProxyRefreshUrlParameter || [{$oViewConf->getRefreshParameter()|@json_encode}];
        window.foun10SmartProxyDebug = window.foun10SmartProxyDebug || [{$oViewConf->getJsDebug()|@json_encode}];
    </script>

    <script type="text/javascript" src="[{$oViewConf->getModuleUrl('foun10SmartProxy', 'out/js/foun10SmartProxy.js')}]"></script>
[{/if}]
