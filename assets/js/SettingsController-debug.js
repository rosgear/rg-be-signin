/*!
 * Контроллер настройки авторизации пользователей.
 * Модуль "Авторизация".
 * Copyright 2015 RosGear. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://rosgear.ru/license/
 */

Ext.define('Rg.be.signin.SettingsController', {
    extend: 'Ge.view.form.PanelController',
    alias: 'controller.rg-be-signin-settings',
    
    /**
     * Срабатывает при клике на флаг "учитывать попытки авторизации".
     * @param {Ext.form.field.Checkbox} me
     * @param {Boolean} value Значение.
     */
     onCheckAttempts: function (me, value) {
        if (value) {
            Ext.getCmp('setting-max-attempts').setDisabled(false);
            Ext.getCmp('setting-fs-send-mail').setDisabled(false);
            Ext.getCmp('setting-fs-disable-id').setDisabled(false);
            Ext.getCmp('setting-fs-black-list').setDisabled(false);
        } else {
            Ext.getCmp('setting-max-attempts').setDisabled(true);
            Ext.getCmp('setting-fs-send-mail').setDisabled(true);
            Ext.getCmp('setting-fs-disable-id').setDisabled(true);
            Ext.getCmp('setting-fs-black-list').setDisabled(true);
        }
    }
});
