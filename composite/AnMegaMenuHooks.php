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

class AnMegaMenuHooks extends an_megamenu
{
    /**
     * List of used hooks
     *
     * @var array
     */
    protected $_hooks = array(
        'actionObjectManufacturerAddAfter',
        'actionObjectManufacturerUpdateAfter',
        'actionObjectManufacturerDeleteAfter',
        'addproduct',
        'updateproduct',
        'deleteproduct',
        'actionCategoryAdd',
        'actionCategoryUpdate',
        'actionCategoryDelete',
        'displayNavFullWidth',
        'displayMobileMenu',
        'displayHeader',
    //    'header',
        'displayTop2',
    );

    /**
     * Menu template
     *
     * @var string
     */
    protected $menuTemplate = 'an_megamenu';

    /**
     * Get list of hooks
     *
     * @var array
     */
	public function getMegaMenuHooks()
	{
		return $this->_hooks;
	}

    /**
     * Hook DisplayHeader
     * needs for PS > 1.7.7.0
     * @return string
     */
    public function hookDisplayHeader()
    {
		$this->context->controller->registerStylesheet(
			$this->name . '-front-css',
			'modules/' . $this->name . '/views/css/front.css',
			array('media' => 'all', 'priority' => 150)
		);

		$this->context->controller->registerJavascript(
			$this->name . '-front-js',
			'modules/' . $this->name . '/views/js/front.js',
			array('position' => 'bottom', 'priority' => 150)
		);
    }
    /**
     * Hook DisplayHeader
     * needs for PS <= 1.7.7.0
     * @return string
     */
    public function hookHeader()
    {
		$this->context->controller->registerStylesheet(
			$this->name . '-front-css',
			'modules/' . $this->name . '/views/css/front.css',
			array('media' => 'all', 'priority' => 150)
		);

		$this->context->controller->registerJavascript(
			$this->name . '-front-js',
			'modules/' . $this->name . '/views/js/front.js',
			array('position' => 'bottom', 'priority' => 150)
		);
    }

    /**
     * Hook DisplayTop
     *
     * @return string
     */
    public function hookDisplayTop()
    {
        if (!$this->isCached('an_megamenu.tpl', $this->getCacheId())) {
            $this->preProcess();
        }
        return $this->display($this->name, $this->menuTemplate . '.tpl', $this->getCacheId());
    }

    /**
     * Hook DisplayNavFullWidth
     *
     * @return string
     */
    public function hookDisplayNavFullWidth()
    {
        return $this->hookDisplayTop();
    }

    public function hookDisplayTop2()
    {
        return $this->hookDisplayTop();
    }

    /**
     * Hook DisplayMobileMenu
     *
     * @return string
     */
    public function hookDisplayMobileMenu()
    {
        if (!$this->isCached('an_megamenu_mobile.tpl', $this->getCacheId())) {
            $this->preProcess();
        }
        return $this->display($this->name, $this->menuTemplate . '_mobile.tpl', $this->getCacheId());
    }
}
