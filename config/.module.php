<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * Файл конфигурации модуля.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

return [
    'translator' => [
        'locale'   => 'auto',
        'patterns' => [
            'text' => [
                'basePath' => __DIR__ . '/../lang',
                'pattern'  => 'text-%s.php'
            ]
        ],
        'autoload' => ['text'],
        'external' => [BACKEND]
    ],

    'accessRules' => [
        // для всех пользователей
        [
            'allow',
            'controllers' => ['Index'],
        ],
        // для авторизованных пользователей Панели управления
        [ // разрешение "Информация о модуле" (info)
            'allow',
            'controllers' => ['Info'],
            'permission'  => 'info',
            'users'       => ['@backend']
        ],
        [ // разрешение "Настройки модуля" (settings)
            'allow',
            'controllers' => ['Settings'],
            'permission'  => 'settings',
            'users'       => ['@backend']
        ],
        [ // для всех остальных, доступа нет
            'deny',
            'exception' => 404
        ]
    ],

    'dataManager' => [
        'settings' => [
            'aliases' => [
                'maxAttempts' => ['maxAttempts' ],
            ]
        ],
        'signin' => [
            'tableName'  => '{{user}}',
            'primaryKey' => 'id',
            'fields'     => [
                ['user_name', 'alias' => 'username', 'title' => 'User name'],
                ['user_password', 'alias' => 'password', 'title' => 'Password']
            ],
            'validationRules' => [
                'checkEmpty' => [['username', 'password'], 'notEmpty'],
                'checkRange' => ['username', 'between', 'min' => 3, 'max' => 10, 'type' => 'string']
            ]
        ]
    ],

    'viewManager' => [
        'id'          => 'rg-signin-{name}',
        'useTheme'    => true,
        'useLocalize' => true,
        'viewMap'     => [
            // информации о модуле
            'info' => [
                'viewFile'      => '//backend/module-info.phtml', 
                'forceLocalize' => true
            ],
            'signin'           => '/layouts/signin.phtml', // макет страницы авторизации
            'signinFooter'     => '/partials/footer.phtml', // нижний колонтитул макета
            'signinFormFooter' => '/partials/form-footer.phtml', // нижний колонтитул формы
            'form'             => '/form.phtml', // форма авторизации
            'mailSignIn'       => '/mails/signin.phtml', // письмо уведомителю (администратору)
            'mailUserSignIn'   => '/mails/signin-mail.phtml', // письмо пользователю
            'settings'         => '/settings.json', // форма настройки модуля
        ]
    ]
];
