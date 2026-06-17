<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * Пакет английской (британской) локализации
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

return [
    '{name}'        => 'Sign in to Control Panel',
    '{description}' => 'Sign in to the website Control Panel',
    '{permissions}' => [],

    // Settings
    '{settings.title}' => 'Module settings',
    // Settings: поля
    'Count attempts' => 'Authorization attempts',
    'Max attempts' => 'Number of sign in attempts',
    'default' => 'default',
    'This is a generic layout that is used to send messages to notification recipients' 
        => 'This is a generic layout that is used to send messages to notification recipients',
    'for users' => 'for users',
    'This is a generic layout that is used to send messages to users' 
        => 'This is a generic layout that is used to send messages to users',
    // Settings: сообщения
    'The description of the email template you selected does not exist or is not in the current subject, this template does not exist' 
        => 'The description of the email template you selected does not exist or is not in the current subject, this template does not exist',

    // Index: шаблон
    'User name' => 'User name',
    'Enter the user name' => 'Enter the user name',
    'Password' => 'Password',
    'Enter the account password' => 'Enter the account password',
    'Sign in' => 'Sign in',
    'Close' => 'Close',
    'Login page' => 'Login page',
    'Help' => 'Help',
    'All right reserved' => 'All right reserved.',
    'version' => 'version',
    'Title recovery account' => 'forgot your password?',
    'Technical support' => 'technical support',
    // Index: шаблон / сообщения
    'You did not fill in the field {0}' => 'You did not fill in the field "{0}"!',
    'The value of the field {0} must be greater than {1} characters' => 'The length of the field value "{0}", must be greater than {1} characters.',
    'The value of the field {0} must be less than {1} characters' => 'The length of the field value "{0}", must be less than {1} characters.',
    'The length of the field value {0} must be between {1} and {2} characters' => 'The length of the field value "{0}", must be between {1} and {2} characters.',
    'The value of the field {0} contains invalid characters' => 'The value of the field"{0}", contains invalid characters.',
    // Index: сообщения
    'You have successfully logged in' => 'You have successfully logged in. Waiting ...',
    // Index: журнал аудита
    'Unsuccessful attempt to authorize the user in the module {module} under the account {user} with the password {password} in {date} the IP address {ipaddress} of the OS {os} in the browser {browser}'
        => 'Unsuccessful attempt to authorize the user in the module «<b>{module}</b>» under the account «<b>{username}</b>» with the password «<b>{password}</b>» in <b>{date}</b> the IP address <b>{ipaddress}</b>, of the OS «<b>{os}</b>» in the browser «<b>{browser}</b>»',
    '{profile} at user account {user} use: {action} from module {module} at {date} from {ipaddress}'
        => '{profile} successful attempt to authorize the user in the module «<b>{module}</b>» under the account «<b>{user}</b>» with the password «<b>{password}</b>» in <b>{date}</b> the IP address <b>{ipaddress}</b>, of the OS «<b>{os}</b>» in the browser «<b>{browser}</b>»',
    'For control, information sent to email {email}' => 'For control, information sent to email «<b>{email}</b>»',
    'Notification about the passage of authorization by the user {user} was sent to him by e-mail {email}' => 'Notification about the passage of authorization by the user «<b>{user}</b>» was sent to him by e-mail «<b>{email}</b>»',

    // Form: сообщения
    'You entered a username or password incorrectly' => 'You entered a username or password incorrectly!',
    'Account error' => 'Account error!',
    'Your account has been disabled' => 'Your account has been disabled!',
    'Go to site' => 'Go to site',
    'You can contact us through' => 'You can contact us through',
    'Title recovery account' => 'Recovery account',
    'You have left {N} {n, plural, =1{attempt} other{attempts}}' 
        => 'You have left <strong>{N}</strong> {n, plural, =1{attempt} other{attempts}}.',
    'IP address blocked after login attempts' => 'IP address blocked after login attempts.',
    'Your IP address is blocked' => 'Your IP address is blocked!',
    'Login to your account on the new device is prohibited' => 'Login to your account on the new device is prohibited!',
    // Form: письмо
    'Attempting to sign in to account «{0}»' => 'Attempting to sign in to account «{0}»',
    'Your IP address is blocked on {0} min' => 'Your IP address is blocked on <b>{0}</b> min!',

    // Mail: шаблон
    'Signed in to your account «{0}»' => 'Signed in to your account «{0}»'    
];
