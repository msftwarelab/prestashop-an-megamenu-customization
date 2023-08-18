<?php
/**
 * 2021 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2021 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

return array(
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'anmenu` (
        `id_anmenu` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop` int(10) unsigned NOT NULL DEFAULT 1,
            `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
            `position` int(10) unsigned NOT NULL DEFAULT 0,
            `label_color` varchar(32) DEFAULT NULL,
            `drop_column` int(10) DEFAULT 0,
            `drop_bgcolor` varchar(32) DEFAULT NULL,
            `drop_bgimage` varchar(128) DEFAULT NULL,
            `bgimage_position` varchar(50) DEFAULT NULL,
            `position_x` int(10) DEFAULT 0,
            `position_y` int(10) DEFAULT 0,
            PRIMARY KEY(`id_anmenu`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8;',
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'anmenu_lang` (
        `id_anmenu` int(10) unsigned NOT NULL,
            `id_lang` int(10) unsigned NOT NULL,
            `name` varchar(254) NOT NULL,
            `link` varchar(254) NOT NULL DEFAULT \'\',
            `label` varchar(128) DEFAULT NULL,
            PRIMARY KEY(`id_anmenu`, `id_lang`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8;',
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'andropdown` (
        `id_andropdown` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_anmenu` int(10) unsigned NOT NULL,
            `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
            `position` int(10) unsigned NOT NULL DEFAULT 0,
            `column` int(10) DEFAULT 0,
            `content_type` varchar(50) NOT NULL,
            `categories` text DEFAULT NULL,
            `products` text DEFAULT NULL,
            `manufacturers` text DEFAULT NULL,
            `drop_bgimage` varchar(128) DEFAULT NULL,
            PRIMARY KEY(`id_andropdown`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8;',
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'andropdown_lang` (
        `id_andropdown` int(10) unsigned NOT NULL,
            `id_lang` int(10) unsigned NOT NULL,
            `static_content` text DEFAULT NULL,
            `title` varchar(254) NOT NULL, 
            PRIMARY KEY(`id_andropdown`, `id_lang`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET = utf8;',
);
