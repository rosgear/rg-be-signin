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
use Ge\Panel\Data\Model\ModuleSettingsModel;

/**
 * Модель настроек модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Rg\Backend\Signin\Model
 * @since 1.0
 */
class Settings extends ModuleSettingsModel
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result, $message) {
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg(Ge::t(BACKEND, 'Settings successfully changed'), $this->t('{settings.title}'), 'accept');
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getDirtyAttributes(array $names = null): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            // Проверять
            'checkAttempts'    => 'checkAttempts', // учитывать попытки авторизации
            'checkDevice'      => 'checkDevice', // вход пользователя на устройстве
            'maxAttempts'      => 'maxAttempts', // Количество попыток
            'mail'             => 'mail', // E-mail получателя уведомлений
            'auditWrite'       => 'auditWrite', // запись действий в журнал аудита
            'templateUserMail' => 'templateUserMail', // Отправить письмо на e-mail получателю уведомлений
            'templateMail'     => 'templateMail', // Шаблон письма
            // Успех авторизации
            'sendUserMailOnSuccess' => 'sendUserMailOnSuccess', // Отправить письмо на e-mail пользователю
            'sendMailOnSuccess'     => 'sendMailOnSuccess', // Отправить письмо на e-mail получателю уведомлений
            // Ошибка авторизации (лимит не исчерпан)
            'sendUserMailOnError' => 'sendUserMailOnError', // Отправить письмо на e-mail пользователю
            'sendMailOnError'     => 'sendMailOnError', // Отправить письмо на e-mail получателю уведомлений
            // Ошибка авторизации (лимит исчерпан) Attempt Limit Reached (ALR)
            'sendUserMailOnALR'       => 'sendUserMailOnALR', // Отправить письмо на e-mail пользователю
            'sendMailOnALR'           => 'sendMailOnALR', // Отправить письмо на e-mail получателю уведомлений
            'disabledUserIp'          => 'disabledUserIp', // Заблокировать IP-адрес пользователя
            'disabledUserIpTime'      => 'disabledUserIpTime', // Заблокировать IP-адрес пользователя на время
            'disabledUserAccount'     => 'disabledUserAccount', // Заблокировать аккаунт пользователя
            'disabledUserAccountTime' => 'disabledUserAccountTime', // Заблокировать аккаунт пользователя на время
            'addToBlackList'          => 'addToBlackList', // Добавить IP-адрес пользователя в черный список
            // Вход пользователя на новом устройстве
            'sendUserMailOnNewDevice' => 'sendUserMailOnNewDevice', // Отправить письмо на e-mail пользователю
            'sendMailOnNewDevice'     => 'sendMailOnNewDevice', // Отправить письмо на e-mail получателю уведомлений
            'banOnNewDevice'          => 'banOnNewDevice', // Запретить авторизацию пользователя на новом устройстве
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatterRules(): array
    {
        return [
            [['checkAttempts', 'checkDevice', 'auditWrite', 'sendUserMailOnSuccess', 
              'sendMailOnSuccess', 'sendUserMailOnError', 'sendMailOnError', 'sendUserMailOnALR', 
              'sendMailOnALR', 'disabledUserAccount', 'disabledUserIp', 'addToBlackList', 
              'sendUserMailOnNewDevice', 'sendMailOnNewDevice', 'banOnNewDevice'], 'logic']
        ];
    }

    /**
     * @param \Ge\Theme\Info\ViewsInfo $views
     * @param string $name
     * 
     * @return bool
     */
    protected function templateValidate($viewsInfo, string $name): bool
    {
        $view = $this->attributes[$name] ?? null;
        // если шаблон не по умолчанию, тогда проверяем все шаблоны писем текущей темы
        if ($view && $view !== 'default') {
            $info = $viewsInfo->getBy('view', $view);
            if ($info === null || !Ge::$app->theme->templateExists($info['filename'])) {
                $this->addError($this->t('The description of the email template you selected does not exist or is not in the current subject, this template does not exist'));
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            // проверка шаблоном писем, т.к. тема может поменяться, а там не будет этих шаблонов
            $views = Ge::$app->createBackendTheme()->getViewsInfo();
            // загружаем описание шаблонов текущей темы
            $views->load();
            // проверяем все шаблоны писем текущей темы
            if (!$this->templateValidate($views, 'templateUserMail')) return false;
            if (!$this->templateValidate($views, 'templateMail')) return false;
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function processing(): void
    {
        // блокировать интерфейс элементов формы если $this->checkAttempts = false (учитывать попытки авторизации)
        $this->response()
            ->meta
                ->cmdComponent('setting-max-attempts', 'setDisabled', [!$this->checkAttempts]) // edit - количество попыток
                ->cmdComponent('setting-fs-send-mail', 'setDisabled', [!$this->checkAttempts]) // fieldset - отправить письмо на e-mail
                ->cmdComponent('setting-fs-disable-id', 'setDisabled', [!$this->checkAttempts]) // fieldset - заблокировать IP-адрес пользователя
                ->cmdComponent('setting-fs-black-list', 'setDisabled', [!$this->checkAttempts]); // fieldset - добавить IP-адрес пользователя
    }
}
