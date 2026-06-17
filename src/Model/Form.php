<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Rg\Backend\Signin\Model;

use Ge;
use Ge\User\User;
use Ge\Config\Config;
use Ge\Data\Model\RecordModel;
use Ge\Panel\User\UserIdentity;
use Ge\Panel\Version\Version as GPanelVersion;

/**
 * Модель авторизации пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Rg\Backend\Signin\Model
 * @since 1.0
 */
class Form extends RecordModel
{
    /**
     * Информация о пользователе.
     * 
     * @see Form::afterValidate()
     * 
     * @var UserIdentity|null
     */
    public ?UserIdentity $identity = null;

    /**
     * E-mail успешно отправлен пользователю.
     * 
     * @see Form::validatePassword()
     * @see Form::validateUserDevice()
     * @see Form::signin()
     * 
     * @var bool
     */
    public bool $mailSentToUser = false;

    /**
     * 
     * E-mail успешно отправлен уведомителю (администратору).
     * 
     * @see Form::validateUserName()
     * @see Form::validatePassword()
     * @see Form::validateUserDevice()
     * @see Form::signin()
     * 
     * @var bool
     */
    public bool $mailSent = false;

    /**
     * Информация о IP-адресе пользователя.
     * 
     * @var \Ge\IpManager\Adapter\AbstractBlockAdapter 
     */
    protected $ipInfo;

    /**
     * {@inheritdoc}
     */
    public function getModelName(): string
    {
        return 'signin';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'username' => $this->t('User name'),
            'password' => $this->t('Password')
        ];
    }

    /**
     * Возвращает имя файла шаблона письма.
     * 
     * @param Config $settings Настройки модуля.
     * @param bool $forUser Шаблона письма предназначен для пользователя, иначе для 
     *     уведомителя (администратора).
     * 
     * @return false|string Если false, указанный в настройках модуля шаблон не существует.
     */
    protected function getMailTemplateFile($settings, bool $forUser = true): false|string
    {
        $template = $forUser ? $settings->templateUserMail : $settings->templateMail;
        if (empty($template)) {
            return false;
        }

        if ($template === 'default') {
            return $forUser ? 'mails/signin-user' : 'mails/signin';
        }
        return Ge::$app->theme->getTemplateFile($template, false);
    }

    /**
     * Отправляет письмо.
     * 
     * @param string $subject Тема письма.
     * @param string $to Кому назначается (указывается через ",").
     * @param string $template Имя шаблона или имя файла шаблона письма.
     * @param array $variables Переменные в шаблоне письма.
     * 
     * @return bool|string Если true, письмо успешно отправлено, иначе сообщение об ошибке.
     */
    protected function sendMail(string $subject, string $to, string $template, array $variables  = []): bool|string
    {
        /** @var \Ge\View\View $view Шаблон письма*/
        $view = Ge::$app->getView();
        $variables['version'] = Ge::$app->version; // версия платформы
        $variables['date']    = Ge::$app->formatter->toDateTime('now') . ' (' . Ge::$app->timeZone->getName() . ')';
        // параметры письма
        $options = [
            'body'    => $view->render($template, $variables),
            'subject' => $subject
        ];
        // адрес "Кому"
        $to = explode(',', $to);
        $addressTo = [];
        foreach ($to as $address) {
            $addressTo[] = ['address' => trim($address)];
        }
        if (sizeof($to) > 1) {
            $options['cc'] = $addressTo;
        } else 
            $options['to'] = $addressTo;
        Ge::$app->mail->isHtml = true;
        return Ge::sendMail($options, true);
    }

    /**
     * Проверяет информацию о IP-адресе пользователя.
     * 
     * @param \Ge\IpManager\Adapter\AbstractBlockAdapter $ipInfo Информация о IP-адресе пользователя.
     * @param Config $settings Настройки модуля.
     * @param string $error
     * 
     * @return string
     */
    protected function checkIpInfo($ipInfo, Config $settings, string $error): string
    {
        if ($ipInfo->id) {
            // если есть ещё попытки авторизации
            if ($ipInfo->attempt > 0) {
                $ipInfo->decreaseAttempt();
                // если лимит исчерпан
                if ($ipInfo->attempt == 0) {
                    // если в настройках установлен флаг "Заблокировать IP-адрес пользователя"
                    if ($settings->disabledUserIp) {
                        // устанавливаем таймаут для IP-адреса
                        $ipInfo->timeout((int) $settings->disabledUserIpTime);
                    } else
                    // если в настройках установлен флаг "Добавить IP-адрес пользователя в черный список"
                    if ($settings->addToBlackList) {
                        $ipInfo->remove();
                        $ipInfo->reset();
                        $black = Ge::$app->ip->list('black');
                        $result = $black->addAddress('current', true, false, $this->module->t('IP address blocked after login attempts'));
                        // TODO: если ошибка, добавить в log, пользователю не выводить
                        /* if ($result === false) { } */
                        return $error = $this->module->t('Your IP address is blocked');
                    // если правил больше нет, идём по кругу
                    } else {
                        $ipInfo->attempt = $settings->maxAttempts;
                    }
                }
                $ipInfo->save();
            } else {
                // если в настройках установлен флаг "Заблокировать IP-адрес пользователя"
                if ($settings->disabledUserIp) {
                    // проверяем, не истёк ли таймаут IP-адреса
                    if ($ipInfo->isTimeout()) {
                        $ipInfo->remove();
                        $ipInfo->reset();
                    }
                }
            }
        // если IP-адрес еще не добавлен, добавляем его
        } else {
            $ipInfo->attempts = $settings->maxAttempts;
            $ipInfo->attempt = $settings->maxAttempts - 1;
            $ipInfo->save();
        }
        // если установлен таймаут
        if ($ipInfo->timeout > 0) {
            $error = $this->module->t('Your IP address is blocked on {0} min', [round($settings->disabledUserIpTime / 60)]);
        } else
        // если установлены попытки авторизации
        if ($ipInfo->attempt > 0) {
            $error = $error . '<br>' . $this->module->t('You have left {N} {n, plural, =1{attempt} other{attempts}}', ['N' => $ipInfo->attempt, 'n' => $ipInfo->attempt]);
        }
        return $error;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            $settings  = $this->module->getSettings();
            $ipAddress = Ge::$app->request->getUserIp();
            // проверка IP-адреса пользователя
            if (!$this->validateIpAddress($ipAddress, $settings)) {
                return false;
            }
            /** @var UserIdentity $identity */
            $identity = Ge::$app->user->createIdentity();
            // проверка имени пользователя
            if (!$this->validateUserName($identity, $this->username, $ipAddress, $settings)) {
                return false;
            }
            // проверка статуса пользователя
            if (!$this->validateUserStatus($identity, $settings)) {
                return false;
            }
            // если установлен модуль "Восстановление аккаунта"
            /** @var \Rg\Backend\Recovery\Helper\Helper $helper */
            $helper = Ge::$app->modules->getObject('Helper\Helper', 'rg.be.recovery');
            if ($helper)
                $token = Ge::$app->request->get($helper->recoveryTokenName);
            else
                $token = null;
            // если указан запрос на восстановления аккаунта
            if ($token) {
                //  проверка записи восстановления аккаунта
                if (!$this->validateUserRecovery($identity, $this->password, $settings, $token, $helper)) {
                    return false;
                }
            } else {
                //  проверка пароля пользователя
                if (!$this->validateUserPassword($identity, $this->password, $settings)) {
                    return false;
                }
            }
            // проверка профиля пользователя
            if (!$this->validateUserProfile($identity, $settings)) {
                return false;
            }
            // проверка роли пользователя
            if (!$this->validateUserRoles($identity, $settings)) {
                return false;
            }
            // проверка устройства пользователя
            if (!$this->validateUserDevice($identity, $settings)) {
                return false;
            }
            //
            $this->identity = $identity;
        }
        return $isValid;
    }

    /**
     * Проверяет IP-адрес пользователя.
     * 
     * @param string $ipAddress IP-адрес пользователя.
     * @param Config $settings Настройки модуля.
     * 
     * @return bool Если true, проверка успешна.
     */
    public function validateIpAddress(string $ipAddress, Config $settings): bool
    {
        $error = null;
        // 1) если в настройках установлен флаг "учитывать попытки авторизации"
        if ($settings->checkAttempts) {
            $blocked = Ge::$app->ip->list('blocked');
            // информация об IP-адресе
            $this->ipInfo = $blocked->ip($ipAddress)->get();
            // если IP-адрес ранее добавлен в список
            if ($this->ipInfo->id) {
                // если таймаут не вышел
                if (!$this->ipInfo->isTimeout()) {
                    $error = $this->module->t('Your IP address is blocked on {0} min', [round($settings->disabledUserIpTime / 60)]);
                }
            }
        }
        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($this->t($error));
            }
            $this->addError($error);
            return false;
        }
        return true;
    }

    /**
     * Проверяет имя пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param string $username Имя пользователя.
     * @param string $ipAddress IP-адрес пользователя.
     * @param Config $settings Настройки моудля.
     * 
     * @return bool Если true, проверка выполнена успешно.
     */
    public function validateUserName(UserIdentity $identity, string $username, string $ipAddress, Config $settings): bool
    {
        /** @var UserIdentity $user */
        $user = $identity->findIdentity(['username' => $username]);
        // ошибка если имя пользователя не существует
        $error = $user === null || $user->isEmpty() ? $this->t('You entered a username or password incorrectly') : null;
        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "учитывать попытки авторизации"
            if ($settings->checkAttempts) {
                if ($this->ipInfo === null) {
                    $blocked = Ge::$app->ip->list('blocked');
                    $this->ipInfo = $blocked->ip($ipAddress)->get();
                }
                $error = $this->checkIpInfo($this->ipInfo, $settings, $error);
                // если попыток не осталось
                if ($this->ipInfo->attempt === 0) {
                    // определяется флагом "Отправить письмо на e-mail получателю уведомлений" при исчерпанном лимите
                    $settings->sendMailOnError = $settings->sendMailOnALR;
                }
            }
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($error,  'username = "' . $username . '"');
            }
            // Отправлять только получателю (администратору), т.к. имя пользователя неизвестно и только, если в настройках 
            // установлен флаг "Отправить письмо на e-mail: получателю уведомлений" и указан его e-mail
            if ($settings->sendMailOnError) {
                // шаблон письма
                $template = $this->getMailTemplateFile($settings, false);
                if ($settings->mail && $template !== false) {
                    $this->mailSent = $this->sendMail(
                        $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                        $settings->mail,
                        $template,
                        [
                            'validate'       => 'username',
                            'invalid'        => 'username',
                            'error'          => $error,
                            'username'       => $username,
                            'user'           => $identity,
                            'profile'        => null,
                            'device'         => $identity->getDevice(),
                            'editionVersion' => Ge::$app->version->getEdition(), // версия редакции
                            'panelVersion'   => new GPanelVersion() // версия панели управления
                        ]
                    );
                }
            }
            $this->addError($error);
            return false;
        }
        return true;
    }

    /**
     * Проверяет пароль пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param string $password Пароль пользователя.
     * @param Config $settings Настройки моудля.
     * @param string $token Токен восстановления аккаунта.
     * @param \Rg\Backend\Recovery\Helper\Helper $helper Помощник восстановления аккаунта.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validateUserRecovery(UserIdentity $identity, string $password, Config $settings, string $token, $helper): bool
    {
        // нет условий для восстановления
        if ($token === null) {
            return true;
        }
        /** @var array|null $recovery */
        $recovery = $helper->getUserRecovery(['user_id' => (int) $identity->id]);
        if ($recovery) {
            if ($token === $recovery['token']) {
                if ($this->validatePassword($identity, $recovery['password'], $password, $settings)) {
                    // сброс пароля
                    $identity->password = $recovery['password'];
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет пароль пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param string $userPassword Пароль пользователя.
     * @param string $password Пароль пользователя.
     * @param Config $settings Настройки моудля.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validatePassword(UserIdentity $identity, string $userPassword, string $password, Config $settings): bool
    {
        $error = !Ge::$app->encrypter->verifyPassword($userPassword, $password);
        $error = $error ? $this->t('You entered a username or password incorrectly') : null;
        $invalid = 'password';
        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "учитывать попытки авторизации"
            if ($settings->checkAttempts) {
                $error = $this->checkIpInfo($this->ipInfo, $settings, $error);
                // если попыток не осталось
                if ($this->ipInfo->attempt === 0) {
                    $invalid = 'attemptLimitReached';
                    // определяется флагом "Отправить письмо на e-mail получателю уведомлений" при исчерпанном лимите
                    $settings->sendMailOnError = $settings->sendMailOnALR;
                    // определяется флагом "Отправить письмо на e-mail пользователю" при исчерпанном лимите
                    $settings->sendUserMailOnError = $settings->sendUserMailOnALR;
                }
            }
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($error, 'password = "' . $password . '"');
            }
            // Отправить письмо получателю (администратору), если в настройках 
            // установлен флаг "Отправить письмо на e-mail: получателю уведомлений" и указан его e-mail
            if ($settings->sendMailOnError) {
                // шаблон письма
                $template = $this->getMailTemplateFile($settings, false);
                if ($settings->mail && $template !== false) {
                    $this->mailSent = $this->sendMail(
                        $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                        $settings->mail,
                        $template,
                        [
                            'validate'       => 'password',
                            'invalid'        => $invalid,
                            'error'          => $error,
                            'username'       => $identity->username,
                            'password'       => $password,
                            'user'           => $identity,
                            'profile'        => $identity->getProfile(),
                            'device'         => $identity->getDevice(),
                            'editionVersion' => Ge::$app->version->getEdition(), // версия редакции
                            'panelVersion'   => new GPanelVersion() // версия панели управления
                        ]
                    );
                }
            }
            // Отправить письмо пользователю (под аккаунтом которого происходит авторизация) если в настройках 
            // установлен флаг "Отправить письмо на e-mail: пользователю" и указан его e-mail
            if ($settings->sendUserMailOnError) {
                // шаблон письма
                $template = $this->getMailTemplateFile($settings, true);
                // профиль пользователя
                $profile = $identity->getProfile();
                if ($profile && $profile->email && $template !== false) {
                    $this->mailSentToUser = $this->sendMail(
                        $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                        $profile->email,
                        $template,
                        [
                            'validate' => 'password',
                            'invalid'  => $invalid,
                            'error'    => $error,
                            'username' => $identity->username,
                            'password' => $password,
                            'user'     => $identity,
                            'profile'  => $profile,
                            'device'   => $identity->getDevice()
                        ]
                    );
                }
            }
            $this->addError($error);
            return false;
        }
        return true;
    }

    /**
     * Проверяет пароль пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param string $password Пароль пользователя.
     * @param Config $settings Настройки моудля.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validateUserPassword(UserIdentity $identity, string $password, Config $settings): bool
    {
        return $this->validatePassword($identity, $identity->password, $password, $settings);
    }

    /**
     * Проверяет статус пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param Config $settings Настройки модуля.
     * 
     * @return bool  Если true, проверка была успешна.
     */
    public function validateUserStatus(UserIdentity $identity, Config $settings): bool
    {
        $error = $identity->status > 0 ? 'Your account has been disabled' : null;
        if ($error) {
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($this->t($error));
            }
            $this->addError($error);
            return false;
        }
        return true;
    }

    /**
     * Проверяет профиль пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param Config $settings Настройки модуля.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validateUserProfile(UserIdentity $identity, Config $settings): bool
    {
        /** @var \Ge\Panel\User\UserProfile $profile */
        $profile = $identity->getProfile();
        $error = ($profile === null || $profile->isEmpty()) ? 'Account error' : false;

        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($this->t($error));
            }
            $this->addError($error  . (GE_DEBUG ? ': user has no profile' : ''));
            return false;
        }
        return true;
    }

    /**
     * Проверяет роли пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param Config $settings Настройки модуля.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validateUserRoles(UserIdentity $identity, Config $settings): bool
    {
        /** @var \Ge\Panel\User $roles */
        $roles = $identity->getRoles();
        /** @var array $rows */
        $rows = $roles->find();
        $error = empty($rows) ? 'Account error' : false;
        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($this->t($error));
            }
            $this->addError($error  . (GE_DEBUG ? ': user has no role' : ''));
            return false;
        }
        return true;
    }

    /**
     * Проверят устройство пользователя.
     * 
     * @param UserIdentity $identity Информация о идентификации пользователя.
     * @param Config $settings Настройки модуля.
     * 
     * @return bool Если true, проверка была успешна.
     */
    public function validateUserDevice(UserIdentity $identity, Config $settings): bool
    {
        $error      = false;
        $device     = $identity->getDevice();
        $userDevice = $device->find();
        $newDevice  = $device->define();
        if ($userDevice) {
            if ($userDevice !== $newDevice) {
                // если запретить авторизацию пользователя на новом устройстве
                if ($settings->banOnNewDevice) {
                    $error = 'Login to your account on the new device is prohibited';
                }
                // Отправить письмо получателю (администратору), если в настройках 
                // установлен флаг "Отправить письмо на e-mail: получателю уведомлений" и указан его e-mail
                if ($settings->sendMailOnNewDevice) {
                    // дата последней авторизации
                    $visitedDate = $identity->visitedDate ? Ge::$app->formatter->toDateTime($identity->visitedDate). ' (' . Ge::$app->formatter->timeZone->getName() . ')' : '';
                    // шаблон письма
                    $template = $this->getMailTemplateFile($settings, false);
                    if ($settings->mail && $template !== false) {
                        $this->mailSent = $this->sendMail(
                            $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                            $settings->mail,
                            $template,
                            [
                                'validate'       => 'device',
                                'invalid'        => 'device',
                                'error'          => null,    
                                'username'       => $identity->username,
                                'visitedDate'    => $visitedDate,
                                'user'           => $identity,
                                'profile'        => $identity->getProfile(),
                                'device'         => $identity->getDevice(),
                                'editionVersion' => Ge::$app->version->getEdition(), // версия редакции
                                'panelVersion'   => new GPanelVersion() // версия панели управления
                            ]
                        );
                    }
                }
                // Отправить письмо пользователю (под аккаунтом которого происходит авторизация) если в настройках 
                // установлен флаг "Отправить письмо на e-mail: пользователю" и указан его e-mail
                if ($settings->sendUserMailOnNewDevice) {
                    // дата последней авторизации
                    $visitedDate = $identity->visitedDate ? 
                                   Ge::$app->formatter->toDateTime($identity->visitedDate). ' (' . Ge::$app->formatter->timeZone->getName() . ')' : '';
                    // шаблон письма
                    $template = $this->getMailTemplateFile($settings, true);
                    // профиль пользователя
                    $profile = $identity->getProfile();
                    if ($profile && $profile->email && $template !== false) {
                        $this->mailSentToUser = $this->sendMail(
                            $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                            $profile->email,
                            $template,
                            [
                                'validate'    => 'device',
                                'invalid'     => 'device',
                                'error'       => null,    
                                'username'    => $identity->username,
                                'visitedDate' => $visitedDate,
                                'user'        => $identity,
                                'profile'     => $identity->getProfile(),
                                'device'      => $identity->getDevice()
                            ]
                        );
                    }
                }
            }
        }
        // если была ошибка
        if ($error) {
            // если в настройках установлен флаг "запись действий в журнал аудита"
            if ($settings->auditWrite) {
                if (Ge::$app->audit->enabled)
                    Ge::$app->audit->info->setError($this->t($error));
            }
            $this->addError($error);
            return false;
        } else {
            $device->update($newDevice);
        }
        return true;
    }

    /**
     * Проверка аккаунта пользователя.
     *
     * @return bool Если значение `true`, успех, иначе ошибка.
     */
    public function signin(): bool
    {
        Ge::$app->user
            ->on(User::EVENT_AFTER_LOGIN, function ($identity) {
                /** @var \Ge\ModuleManager\ModuleRegistry $installed */
                $installed = Ge::$app->modules->getRegistry();
                /* Внимание: если файл конфигурации модулей отсутствует, то он будет создан */
                if (!$installed->hasUpdated()) {
                    $installed->update();
                }

                /** @var \Ge\ExtensionManager\ExtensionRegistry $installed */
                $installed = Ge::$app->extensions->getRegistry();
                /* Внимание: если файл конфигурации расширений отсутствует, то он будет создан */
                if (!$installed->hasUpdated()) {
                    $installed->update();
                }

                $settings  = $this->module->getSettings();
                // Отправить письмо получателю (администратору), если в настройках 
                // установлен флаг "Отправить письмо на e-mail: получателю уведомлений" и указан его e-mail
                if ($settings->sendUserMailOnSuccess) {
                    // шаблон письма
                    $template = $this->getMailTemplateFile($settings, true);
                    if ($settings->mail && $template !== false) {
                        $this->mailSent = $this->sendMail(
                            $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                            $settings->mail,
                            $template,
                            [
                                'validate' => 'success',
                                'invalid'  => null,
                                'error'    => null,    
                                'username' => $identity->username,
                                'user'     => $identity,
                                'profile'  => $identity->getProfile(),
                                'device'   => $identity->getDevice(),
                            ]
                        );
                    }
                }
                // Отправить письмо пользователю (под аккаунтом которого происходит авторизация) если в настройках 
                // установлен флаг "Отправить письмо на e-mail: пользователю"
                if ($settings->sendMailOnSuccess) {
                    // шаблон письма
                    $template = $this->getMailTemplateFile($settings, false);
                    $profile = $identity->getProfile();
                    // если пользователь имеет e-mail
                    if ($profile && $profile->email && $template !== false) {
                        $this->mailSentToUser = $this->sendMail(
                            $this->module->t('Attempting to sign in to account «{0}»', [Ge::$app->version->name]),
                            $profile->email,
                            $template,
                            [
                                'validate'       => 'success',
                                'invalid'        => null,
                                'error'          => null,    
                                'username'       => $identity->username,
                                'user'           => $identity,
                                'profile'        => $identity->getProfile(),
                                'device'         => $identity->getDevice(),
                                'editionVersion' => Ge::$app->version->getEdition(), // версия редакции
                                'panelVersion'   => new GPanelVersion() // версия панели управления
                                ]
                        );
                    }
                }

                /** @var \Rg\Backend\Recovery\Helper\Helper $helper */
                $helper = Ge::$app->modules->getObject('Helper\Helper', 'rg.be.recovery');
                if ($helper) {
                    // удаляем запись восстановления если она есть
                    $helper->resetUserRecovery((int) $identity->id);
                }

                // удаляем IP-адрес пользователя если он был временно заблокирован
                $blocked = Ge::$app->ip->list('blocked');
                $blocked->ip('current')->remove();

                // сторона авторизации пользователя
                $identity->setSide(BACKEND_SIDE_INDEX);
                // дата авторизации
                $identity->visitedDate = Ge::$app->formatter->makeDate('Y-m-d H:i:s', Ge::$app->dataTimeZone);
                $identity->update();
                // запись параметров авторизации
                $identity->write();
            })
            ->login($this->identity);

        return true;
    }
}
