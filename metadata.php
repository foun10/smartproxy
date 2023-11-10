<?php

use foun10\SmartProxy\Extension\Core\Header;
use foun10\SmartProxy\Extension\Core\Session;
use foun10\SmartProxy\Extension\Core\ShopControl;
use foun10\SmartProxy\Extension\Core\ViewConfig;
use foun10\SmartProxy\Extension\Application\Model\Article;
use foun10\SmartProxy\Extension\Core\Cache\DynamicContent\ContentCache;

$sMetadataVersion = '2.1';

$aModule = [
    'id' => 'foun10SmartProxy',
    'title' => '<img src="../modules/foun10/foun10.png" style="height: 15px; position: relative; top: 2px;margin-right:5px" alt="foun10" />foun10 - Logiken für das Scale Smartproxy Caching',
    'description' => '',
    'thumbnail' => '../foun10.png',
    'version' => '1.0.2',
    'author' => 'foun10 GmbH',
    'email' => 'info@foun10.de',
    'extend' => [
        \OxidEsales\Eshop\Core\Header::class => Header::class,
        \OxidEsales\Eshop\Core\Session::class => Session::class,
        \OxidEsales\Eshop\Core\ShopControl::class => ShopControl::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => ViewConfig::class,
        \OxidEsales\Eshop\Application\Model\Article::class => Article::class,
        \OxidEsales\Eshop\Core\Cache\DynamicContent\ContentCache::class => ContentCache::class,
    ],
    'controllers' => [
        'foun10SmartProxyEnvironmentSetter' => \foun10\SmartProxy\Controller\EnvironmentSetter::class,
        'foun10SmartProxyTrackingData' => \foun10\SmartProxy\Controller\TrackingData::class,
        'foun10SmartProxyAdminCache' => \foun10\SmartProxy\Controller\Admin\AdminCacheController::class,
    ],
    'templates' => [
        'foun10/SmartProxy/base__head.tpl' => 'foun10/SmartProxy/views/tpl/foun10/SmartProxy/base__head.tpl',
        'foun10/SmartProxy/admin/cache.tpl' => 'foun10/SmartProxy/views/admin/tpl/cache.tpl',
    ],
    'blocks' => [
        [
            'template' => 'layout/base.tpl',
            'block' => 'head_css',
            'file' => 'views/blocks/base__head.tpl'
        ],
    ],
    'settings' => [
        [
            'group' => 'FOUN10_SMART_PROXY_MAIN',
            'name' => 'FOUN10_SMART_PROXY_ACTIVE_GENERAL',
            'type' => 'bool',
            'value' => false,
        ], [
            // Define which frontend controllers should prevent smartproxy caching
            'group' => 'FOUN10_SMART_PROXY_MAIN',
            'name' => 'FOUN10_SMART_PROXY_CACHEABLE_CONTROLLERS',
            'type' => 'arr',
            'value' => [
                'start',
                'alist',
                'details',
                'info',
                'search',
                'content',
            ],
        ], [
            // Define which url parameters should prevent smartproxy caching
            'group' => 'FOUN10_SMART_PROXY_MAIN',
            'name' => 'FOUN10_SMART_PROXY_NON_CACHEABLE_PARAMETERS',
            'type' => 'arr',
            'value' => [
                'nocache=1',
            ],
        ], [
            // Define which url parameter should initiate a cookie refresh even if page was loaded by cache - JS logic
            'group' => 'FOUN10_SMART_PROXY_MAIN',
            'name' => 'FOUN10_SMART_PROXY_REFRESH_PARAMETER',
            'type' => 'arr',
            'value' => [
                'fnc=logout',
                'listorderby',
                'listorder',
            ],
        ], [
            // JS debug output ?
            'group' => 'FOUN10_SMART_PROXY_MAIN',
            'name' => 'FOUN10_SMART_PROXY_JS_DEBUG',
            'type' => 'bool',
            'value' => false,
        ], [
            // Rundeck subdomain
            'group' => 'FOUN10_SMART_PROXY_RUNDECK',
            'name' => 'FOUN10_SMART_PROXY_RUNDECK_SUBDOMAIN',
            'type' => 'str',
            'value' => '',
        ], [
            // Rundeck auth token
            'group' => 'FOUN10_SMART_PROXY_RUNDECK',
            'name' => 'FOUN10_SMART_PROXY_RUNDECK_AUTHTOKEN',
            'type' => 'str',
            'value' => '',
        ], [
            // Rundeck job id for complete html cache clear
            'group' => 'FOUN10_SMART_PROXY_RUNDECK',
            'name' => 'FOUN10_SMART_PROXY_RUNDECK_JOB_HTML_CLEAR',
            'type' => 'str',
            'value' => '',
        ], [
            // Rundeck job id for cache clear by search tag
            'group' => 'FOUN10_SMART_PROXY_RUNDECK',
            'name' => 'FOUN10_SMART_PROXY_RUNDECK_JOB_TAG_CLEAR',
            'type' => 'str',
            'value' => '',
        ],
    ],
];
