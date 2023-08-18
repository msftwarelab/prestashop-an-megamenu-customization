<?php
/**
 * 2007-2017 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AnMegaMenuAjaxHandler extends an_megamenu
{
    /**
     * Get product list
     */
    protected function ajaxProductsList()
    {
        $query = Tools::getValue('q', false);
        if (!$query || $query == '' || Tools::strlen($query) < 1) {
            die();
        }
        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int)Context::getContext()->language->id . Shop::addSqlRestrictionOnLang('pl') . ')
            WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')
            GROUP BY p.`id_product`';

        $items = Db::getInstance()->executeS($sql);

        if ($items) {
            foreach ($items as $item) {
                echo trim($item['name']) . (!empty($item['reference']) ? ' (ref: ' . $item['reference'] . ')' : '') . '|' . (int)$item['id_product'] . "\n";
            }
        } else {
            json_encode(new stdClass());
        }
    }

    /**
     * Change menu status
     */
    protected function ajaxStatusMenu()
    {
        $result = array(
            'success' => false,
            'error' => true,
            'text' => $this->tr('Failed to update the status')
        );

        $id_anmenu = (int)Tools::getValue('id_anmenu');
        if ($id_anmenu) {
            $anmenu = new AnMenu($id_anmenu);
            $anmenu->active = !(int)$anmenu->active;
            if ($anmenu->save()) {
                $this->_clearCache('*');
                $result = array(
                    'success' => true,
                    'text' => $this->tr('The status has been updated successfully')
                );
            } else {
                $result = array(
                    'success' => false,
                    'error' => true,
                    'text' => $this->tr('Failed to update the status')
                );
            }
        }

        die(json_encode($result));
    }

    /**
     * Update menu position
     */
    protected function ajaxPositionsMenu()
    {
        $positions = Tools::getValue('anmenu');

        if (empty($positions)) {
            return;
        }

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2])) {
                AnMenu::updatePosition($pos[2], $position + 1);
            }
        }

        $this->_clearCache('*');
    }

    /**
     * Change dropdown status
     */
    protected function ajaxStatusDropdown()
    {
        $result = array(
            'success' => false,
            'error' => true,
            'text' => $this->tr('Failed to update the status')
        );

        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $id_andropdown = (int)Tools::getValue('id_andropdown');
        if ($id_andropdown) {
            $andropdown = new AnDropdown($id_anmenu, $id_andropdown);
            $andropdown->active = !(int)$andropdown->active;
            if ($andropdown->save()) {
                $this->_clearCache('*');
                $result = array(
                    'success' => true,
                    'text' => $this->tr('The status has been updated successfully')
                );
            } else {
                $result = array(
                    'success' => false,
                    'error' => true,
                    'text' => $this->tr('Failed to update the status')
                );
            }
        }

        die(json_encode($result));
    }

    /**
     * Update dropdown position
     */
    protected function ajaxPositionsDropdown()
    {
        $positions = Tools::getValue('andropdown');
        if (empty($positions)) {
            return;
        }

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);
            if (isset($pos[2])) {
                AnDropdown::updatePosition($pos[2], $position + 1);
            }
        }

        $this->_clearCache('*');
    }
}
