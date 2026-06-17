<?php
/**
 * Этот файл является частью модуля веб-приложения RosGear.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Rg\Backend\Signin\Controller;

use Ge;
use Ge\Panel\Helper\ExtCombo;
use Ge\Panel\Widget\SettingsWindow;
use Ge\Panel\Controller\ModuleSettingsController;

/**
 * Контроллер настроек модуля.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Rg\Backend\Signin\Controller
 * @since 1.0
 */
class Settings extends ModuleSettingsController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'Settings';

    /**
     * {@inheritdoc}
     */
    public function createWidget(): SettingsWindow
    {
        $mailTemplates = [];

        /** @var \Ge\Theme\Info\ViewsInfo $viewsInfo */
        $viewsInfo = Ge::$app->createBackendTheme()->getViewsInfo();
        if ($viewsInfo->load()) {
            /** @var array $mailTemplates Шаблоны писем */
            $mailTemplates = $viewsInfo->find(['type' => 'mail'], true, ['view', 'description']);
        }
        array_unshift($mailTemplates, ['default', $this->t('default')]);

        /** @var SettingsWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 500;
        $window->height = 700;
        $window->responsiveConfig = [
            'height < 700' => ['height' => '99%'],
            'width < 500' => ['width' => '99%'],
        ];

        // панель формы (Ge.view.form.Panel GeJS)
        $window->form->autoScroll = true;
        $window->form->bodyPadding = 0;
        $window->form->controller = 'rg-be-signin-settings';
        // шаблон для текущей версии языка
        $window->form->forceLocalize = true;
        $window->form->useLocalize = true;
        $window->form->loadJSONFile(
            '/settings',
            'items',
            [
                // шаблоны писем для получаталей уведомлений
                '@templateMail' => ExtCombo::themeViews(
                    '#for notification recipients', 
                    'templateMail', 
                    BACKEND, 
                    ['type' => 'mail'],
                    ['view' => 'default', 'description' => $this->t('default')],
                    ['tooltip' => '#This is a generic layout that is used to send messages to notification recipients']
                ),
                // шаблоны писем для пользователей
                '@templateUserMail' => ExtCombo::themeViews(
                    '#for users', 
                    'templateUserMail', 
                    BACKEND, 
                    ['type' => 'mail'],
                    ['view' => 'default', 'description' => $this->t('default')],
                    ['tooltip' => '#This is a generic layout that is used to send messages to users']
                )
            ]
        );

        /** @var \Ge\Panel\Http\Response $response */
        $response = $this->getResponse();
        $response
            ->meta
                ->add('jsPath', ['Rg.be.signin', $this->module->getRequireUrl() . '/js'])
                ->add('requires', 'Rg.be.signin.SettingsController')
                ->add('requires', 'Ge.view.form.field.Field')
                ->add('css', $window->cssSrc('/signin.css'));
        return $window;
    }
}
