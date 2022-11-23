[{include file="headitem.tpl" title="FOUN10_SMARTPROXY_CACHE_TITLE"|oxmultilangassign}]

<style type="text/css">
    .smartproxy-cache-wrapper {
        margin-top: 20px;
        line-height: 120%;
        padding: 10px;
    }

    .smartproxy-cache-clear-tags {
        margin-bottom: 30px;
    }

    .smartproxy-cache-clear-tags__tag {
        margin-bottom: 10px;
    }

    div.export.success {
        background-color: #009503;
        margin-top: 20px;
    }
</style>

<div class="export">
    [{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_TITLE"}]
</div>

[{if $success}]
    <div class="export success">
        [{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_SUCCESS"}]
    </div>
[{/if}]

<div class="smartproxy-cache-wrapper">
    [{assign var="cacheTags" value=$oView->getAllCacheTags()}]

    [{if $cacheTags|@count > 0}]
        <div class="smartproxy-cache-clear-tags">
            <form action="[{$oViewConf->getSelfLink()}]" method="post">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="foun10SmartProxyAdminCache">
                <input type="hidden" name="fnc" value="clearCacheByTags">

                <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                        <tr>
                            <td class="edittext" width="320" valign="top">
                                [{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_BY_TAG_TYPES"}]
                            </td>
                            <td class="edittext" valign="top">
                                [{foreach from=$cacheTags item="cacheTag"}]
                                    <div class="smartproxy-cache-clear-tags__tag">
                                        <input type="checkbox" name="clear[]" value="[{$cacheTag}]" id="cache_tag_[{$cacheTag}]" />
                                        <label for="cache_tag_[{$cacheTag}]">[{oxmultilang ident="CACHE_TAG_"|cat:$cacheTag|upper}]</label>
                                        <br />
                                    </div>
                                [{/foreach}]
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext" width="320" height="40" valign="middle">
                                [{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_BY_TAGS"}]
                            </td>
                            <td class="edittext" valign="middle">
                                <input type="text" name="clearByDefinedTags" value="" style="width: 200px;" />
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext" width="320" height="40">
                            </td>
                            <td class="edittext" valign="middle">
                                <input type="submit" class="edittext" style="width: 210px;" value="[{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_BY_BUTTON"}]">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    [{/if}]

    <div class="smartproxy-cache-clear-all">
        <form action="[{$oViewConf->getSelfLink()}]" method="post">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="foun10SmartProxyAdminCache">
            <input type="hidden" name="fnc" value="clearAllCaches">

            <table cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr>
                    <td class="edittext" width="320" height="40" valign="middle">
                        [{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_ALL"}]
                    </td>
                    <td class="edittext" valign="middle">
                        <input type="submit" class="edittext" style="width: 210px;" value="[{oxmultilang ident="FOUN10_SMARTPROXY_CACHE_CLEAR_ALL_BUTTON"}]">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>

</div>

[{include file="bottomitem.tpl"}]
