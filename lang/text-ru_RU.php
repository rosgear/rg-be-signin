<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * Пакет русской локализации
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

return [
    '{name}'        => 'Авторизация в Панели управления',
    '{description}' => 'Авторизация в Панели управления веб-сайтом',
    '{permissions}' => [],

    // Settings
    '{settings.title}' => 'Настройка модуля',
    // Settings: поля
    'Count attempts' => 'Попыток авторизации',
    'Max attempts' => 'Количество попыток авторизации',
    'default' => 'по умолчанию',
    'for notification recipients' => 'для получателей уведомлений',
    'This is a generic layout that is used to send messages to notification recipients' 
        => 'Это универсальный макет, который используется для рассылки сообщений получателей уведомлений',
    'for users' => 'для пользователей',
    'This is a generic layout that is used to send messages to users' 
        => 'Это универсальный макет, который используется для рассылки сообщений пользователям',
    // Settings: сообщения
    'The description of the email template you selected does not exist or is not in the current subject, this template does not exist' 
        => 'Описание выбранного вами шаблона письма нет в текущей теме или файл шаблона отсутствует',
    // Index: шаблон
    'User name' => 'Имя пользователя',
    'Enter the user name' => 'Введите имя пользователя',
    'Password' => 'Пароль',
    'Enter the account password' => 'Введите пароль',
    'Sign in' => 'Войти',
    'Close' => 'Закрыть',
    'Login page' => 'Страница авторизации',
    'Help' => 'Справка',
    'All right reserved' => 'Все права защищены.',
    'version' => 'версия',
    'Title recovery account' => 'забыли свой пароль?',
    'Technical support' => 'техподдержка',
    // Index: шаблон / сообщения
    'You did not fill in the field {0}' => 'Вы не заполнили поле "{0}"!',
    'The value of the field {0} must be greater than {1} characters' => 'Длина значения поля "{0}", должна быть больше {1} символов.',
    'The value of the field {0} must be less than {1} characters' => 'Длина значения поля "{0}", должна быть меньше {1} символов.',
    'The length of the field value {0} must be between {1} and {2} characters' => 'Длина значения поля "{0}", должна быть от {1} до {2} символов.',
    'The value of the field {0} contains invalid characters' => 'Значение поля "{0}", содержит некорректные символы.',
    // Index: сообщения
    'You have successfully logged in' => 'Вы успешно прошли авторизацию. Ожидайте ...',
    // Index: журнал аудита
    'Unsuccessful attempt to authorize the user in the module {module} under the account {user} with the password {password} in {date} the IP address {ipaddress} of the OS {os} in the browser {browser}'
        => 'Безуспешная попытка пройти пользователем авторизацию в модуле «<b>{module}</b>» под учётной записью «<b>{user}</b>» c паролем «<b>{password}</b>» в <b>{date}</b> c IP-адреса <b>{ipaddress}</b>, операционной системы «<b>{os}</b>» в браузере «<b>{browser}</b>»',
    '{profile} at user account {user} use: {action} from module {module} at {date} from {ipaddress}'
        => '<b>{profile}</b> успешно прошел авторизацию в модуле «<b>{module}</b>» под учётной записью пользователя «<b>{user}</b>» c паролем «<b>{password}</b>» в <b>{date}</b> c IP-адреса <b>{ipaddress}</b>, операционной системы «<b>{os}</b>» в браузере «<b>{browser}</b>»',
    'For control, information sent to email {email}' 
        => 'Для контроля, информация отправлена на email «<b>{email}</b>»',
    'Notification about the passage of authorization by the user {user} was sent to him by e-mail {email}' 
        => 'Уведомление о прохождении авторизации пользователем «<b>{user}</b>», отправлено ему на e-mail «<b>{email}</b>»',

    // Form: сообщения
    'You entered a username or password incorrectly' => 'Вы неправильно ввели логин или пароль!',
    'Account error' => 'Ошибка учётной записи!',
    'Your account has been disabled' => 'Ваш аккаунт был отключен!',
    'You have left {N} {n, plural, =1{attempt} other{attempts}}' 
        => 'У вас осталось <strong>{N}</strong> {n, plural, one{попытка} few{попытки} many{попыток} other{попыток}}.', 
    'IP address blocked after login attempts' => 'IP-адрес заблокирован по истечении попыток авторизации.',
    'Your IP address is blocked' => 'Ваш IP-адрес заблокирован!',
    'Login to your account on the new device is prohibited' => 'Вход в Ваш аккаунт на новом устройстве запрещен!',
    // Form: письмо
    'Attempting to sign in to account «{0}»' => 'Попытка входа в аккаунт «{0}»',
    'Your IP address is blocked on {0} min' => 'Ваш IP-адрес заблокирован на <strong>{0}</strong> мин.!',

    // Mail: шаблон
    'Signed in to your account «{0}»' => 'Выполнен вход в Ваш аккаунт «{0}»'
];
