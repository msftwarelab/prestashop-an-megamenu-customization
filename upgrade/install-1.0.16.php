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

function upgrade_module_1_0_16($object, $install = false)
{
    $sql = array();

    $sql[] = '
		ALTER TABLE `' . _DB_PREFIX_ . 'andropdown_lang` ADD `title` VARCHAR(255) NOT NULL AFTER `static_content`;	
    ';

    foreach ($sql as $_sql) {
        Db::getInstance()->Execute($_sql);
    }

    return true;
}
