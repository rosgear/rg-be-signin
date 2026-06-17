<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * Файл конфигурации установки модуля.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

return [
    'use'         => BACKEND,
    'id'          => 'rg.be.signin',
    'name'        => 'Sign in to Control Panel',
    'description' => 'Sign in to the website Control Panel',
    'namespace'   => 'Rg\Backend\Signin',
    'path'        => '/rg/rg.be.signin',
    'route'       => 'signin',
    'routes'      => [
        [
            'type'    => 'crudSegments',
            'options' => [
                'module'      => 'rg.be.signin',
                'route'       => 'signin',
                'prefix'      => BACKEND,
                'childRoutes' => [
                    'verify' => [
                        'route'    => 'verify',
                        'defaults' => ['action' => 'verify']
                    ]
                ]
            ]
        ]
    ],
    'locales'     => ['ru_RU', 'en_GB'],
    'permissions' => ['info', 'settings'],
    'events'      => [],
    'required'    => [
        ['php', 'version' => '8.2'],
        ['app', 'code' => 'RG Workspace'],
        ['app', 'code' => 'RG CMS'],
        ['app', 'code' => 'RG CRM'],
    ]
];
