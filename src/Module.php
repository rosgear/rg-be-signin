<?php
/**
 * Модуль веб-приложения RosGear.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Rg\Backend\Signin;

/**
 * Модуль авторизации пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Rg\Backend\Signin
 * @since 1.0
 */
class Module extends \Ge\Panel\Module\Module
{
    /**
     * {@inheritdoc}
     */
    public string $id = 'rg.be.signin';

    /**
     * {@inheritdoc}
     */
    public string|array $layout = 'signin';
}
