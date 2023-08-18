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

require_once _PS_MODULE_DIR_ . 'an_megamenu/classes/AnMenu.php';
require_once _PS_MODULE_DIR_ . 'an_megamenu/classes/AnDropdown.php';

/**
 * Class an_megamenu
 */
class an_megamenu extends Module
{
    const PREFIX = "an_megamenu_";
	/**
     * Composition classes
     */
    protected $compositeClasses = array(
        'AnMegaMenuConfigurator',
        'AnMegaMenuMenuConfigurator',
        'AnMegaMenuHooks',
        'AnMegaMenuAjaxHandler',
        'AnMegaMenuDropDownConfigurator',
    );

    /**
     * Composition cache
     */
    protected static $compositeCache = array();

    /**
     * @var string
     */
    protected $bg_img_folder = 'views/img/images/';

    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $currentIndex;

    /**
     * an_megamenu constructor.
     */
    public function __construct()
    {
        $this->name = 'an_megamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.0.19';
        $this->author = 'Anvanto';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->tr('Anvanto Mega Menu');
        $this->description = $this->tr('Anvanto Mega Menu');
        $this->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
    }

    /**
     * Installation
     *
     * @return bool
     */
    public function install()
    {
        $sql = include _PS_MODULE_DIR_ . 'an_megamenu/sql/install.php';
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
		
		if (!$this->getParam('do_not_install_samples')){
			$this->installSampleData();
		}
		
		if (parent::install()) {
            foreach ($this->getMegaMenuHooks() as $hook) {
                if (!$this->registerHook($hook)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
	
    public function getParamList()
    {
        return array(
            'do_not_install_samples',
        );
    }	
	
    public function getParam($key, $id_lang = 0)
    {
        return Configuration::get(self::PREFIX.$key, $id_lang);
    }
	
    protected function updateParam($key, $value)
    {
        return Configuration::updateValue(self::PREFIX.$key, $value);
    }	

    /**
     * Sample data
     *
     * @return bool
     */
    public function installSampleData()
    {
        if (Tools::file_exists_no_cache(_PS_MODULE_DIR_ . 'an_megamenu/sql/sample.php')) {
			$sample = include _PS_MODULE_DIR_ . 'an_megamenu/sql/sample.php';
		} else {
			return false;
		}
		
		foreach ($sample as $key => $item){
		
			$newMenu = new AnMenu();
			$languages = Language::getLanguages(true, false, true);
			foreach ($languages as $key => $langId){
				$newMenu->name[$langId] = $item['name'];
			}
			$newMenu->save();
		}
    }

    /**
     * Uninstallation
     *
     * @return bool
     */
    public function uninstall()
    {
        $sql = include _PS_MODULE_DIR_ . 'an_megamenu/sql/uninstall.php';
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return parent::uninstall();
    }

    /**
     * Translator
     *
     * @param $text
     * @param array $params
     * @param string $type
     * @return mixed
     */
    protected function tr($text, $params = array(), $type = 'Modules.AnMegaMenu.Admin')
    {
        return $this->getTranslator()->trans($text, $params, $type);
    }

    /**
     * @return mixed|void
     */
    public function __call($method, $args = array())
    {
        if (empty(static::$compositeCache)) {
            foreach ($this->compositeClasses as $class) {
                require_once _PS_MODULE_DIR_ . 'an_megamenu/composite/' . $class . '.php';
                static::$compositeCache[$class] = new $class();
            }
        }

        foreach (static::$compositeCache as $instance) {
            if (method_exists($instance, $method)) {
                return $instance->$method();
            }
        }

        $this->_clearCache('*');
    }

    /**
     * Get configuratino
     *
     * @return string
     */
    public function getContent()
    {
        $stores = Shop::getContextListShopID();
        if (count($stores) > 1) {
            //$this->errors[] = Tools::displayError($this->l('Please select shop!'));
            return Tools::displayError($this->l('Please select shop!'));
        }
        return $this->getConfiguration();
    }

    protected function getCMSCategories($recursive = false, $parent = 1, $id_lang = false, $id_shop = false)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $id_shop = ($id_shop !== false) ? $id_shop : Context::getContext()->shop->id;
        $join_shop = '';
        $where_shop = '';

        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $join_shop = ' INNER JOIN `'._DB_PREFIX_.'cms_category_shop` cs
			ON (bcp.`id_cms_category` = cs.`id_cms_category`)';
            $where_shop = ' AND cs.`id_shop` = '.(int)$id_shop.' AND cl.`id_shop` = '.(int)$id_shop;
        }

        if ($recursive === false) {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'cms_category` bcp'.$join_shop.'
				INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
				ON (bcp.`id_cms_category` = cl.`id_cms_category`)
				WHERE cl.`id_lang` = '.(int)$id_lang.'
				AND bcp.`id_parent` = '.(int)$parent.$where_shop;

            return Db::getInstance()->executeS($sql);
        } else {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'cms_category` bcp'.$join_shop.'
				INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
				ON (bcp.`id_cms_category` = cl.`id_cms_category`)
				WHERE cl.`id_lang` = '.(int)$id_lang.'
				AND bcp.`id_parent` = '.(int)$parent.$where_shop;

            $results = Db::getInstance()->executeS($sql);
            foreach ($results as $result) {
                $sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], (int)$id_lang);
                if ($sub_categories && count($sub_categories) > 0) {
                    $result['sub_categories'] = $sub_categories;
                }
                $categories[] = $result;
            }

            return isset($categories) ? $categories : false;
        }
    }

    protected function getCMSPages($id_cms_category = false, $id_shop = false, $id_lang = false)
    {
        $id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        $where_shop = '';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $where_shop = ' AND cl.`id_shop` = '.(int)$id_shop;
        }

        $where = $id_cms_category ? 'c.`id_cms_category` = '.(int)$id_cms_category.' AND ' : '';
        $sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE '.$where.' cs.`id_shop` = '.(int)$id_shop.'
			AND cl.`id_lang` = '.(int)$id_lang.$where_shop.'
			AND c.`active` = 1
			ORDER BY `position`';

        return Db::getInstance()->executeS($sql);
    }

    protected function getCMSCategory($id = false, $id_lang = false)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $join_shop = '';

        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $join_shop = ' INNER JOIN `'._DB_PREFIX_.'cms_category_shop` cs
			ON (bcp.`id_cms_category` = cs.`id_cms_category`)';
        }

        $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
            FROM `'._DB_PREFIX_.'cms_category` bcp'.$join_shop.'
            INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
            ON (bcp.`id_cms_category` = cl.`id_cms_category`)
            WHERE cl.`id_lang` = '.(int)$id_lang.'
            AND bcp.`id_cms_category` = '.(int)$id;

        return Db::getInstance()->executeS($sql)[0];
    }

    protected function getCategoryForMenu($id_cms_category, $id_lang = false) {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        $subCategories = $this->getCMSCategories(false, $id_cms_category, $id_lang);
        $subPages = $this->getCMSPages($id_cms_category);

        return array('subcategorues'=>$subCategories, 'subpages'=>$subPages);
    }

    protected function getCMSPagesForMenu($pagesArray = false, $id_shop = false, $id_lang = false)
    {
        if(!$pagesArray){return array();}
        $id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        $where_shop = '';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $where_shop = ' AND cl.`id_shop` = '.(int)$id_shop;
        }

        $sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE c.id_cms IN (' . implode(', ', $pagesArray) . ')
			AND cs.`id_shop` = '.(int)$id_shop.'
			AND cl.`id_lang` = '.(int)$id_lang.$where_shop.'
			AND c.`active` = 1
			ORDER BY `position`';

        return Db::getInstance()->executeS($sql);
    }
}
