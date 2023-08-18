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

class AnDropdown extends ObjectModel
{
    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $id_andropdown;
    /**
     * @var
     */
    public $id_anmenu;
    /**
     * @var int
     */
    public $active = 1;
    /**
     * @var int
     */
    public $position;
    /**
     * @var int
     */
    public $column = 1;
    /**
     * @var
     */
    public $content_type; // none, category, product, html, manufacturer

    /**
     * @var string
     */
    public $categories = 'a:0:{}';
    /**
     * @var string
     */
    public $products = 'a:0:{}';
    /**
     * @var string
     */
    public $manufacturers = 'a:0:{}';
    /**
     * @var
     */
    public $static_content;
    /**
     * @var
     */
    public $drop_bgimage;    /**
     * @var
     */
    public $title;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'andropdown',
        'primary' => 'id_andropdown',
        'multilang' => true,
        'multilang_shop' => false,
        'fields' => array(
            'id_anmenu' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'position' => array('type' => self::TYPE_INT),
            'column' => array('type' => self::TYPE_INT),
            'content_type' => array('type' => self::TYPE_STRING, 'size' => 50),
            'categories' => array('type' => self::TYPE_STRING),
            'products' => array('type' => self::TYPE_STRING),
            'manufacturers' => array('type' => self::TYPE_STRING),
            'static_content' => array('type' => self::TYPE_HTML, 'lang' => true),
            'drop_bgimage' => array('type' => self::TYPE_STRING, 'size' => 128),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 50,  'validate' => 'isCleanHtml'),
        ),
    );

    /**
     * AnDropdown constructor.
     * @param $id_anmenu
     * @param null $id_andropdown
     * @param null $id_lang
     */
    public function __construct($id_anmenu, $id_andropdown = null, $id_lang = null)
    {
        parent::__construct($id_andropdown, $id_lang);

        if ($id_anmenu) {
            $this->id_anmenu = $id_anmenu;
        }

        $this->categories = Tools::unSerialize($this->categories);
        $this->products = Tools::unSerialize($this->products);
        $this->manufacturers = Tools::unSerialize($this->manufacturers);
        if(!$this->categories) {
            $this->categories = array();
        }
        if(!$this->products) {
            $this->products = array();
        }
        if(!$this->manufacturers) {
            $this->manufacturers = array();
        }

        if (!$this->position) {
            $this->position = 1 + $this->getMaxPosition();
        }
    }

    /**
     * @param bool $null_values
     * @param bool $autodate
     * @return mixed
     */
    public function save($null_values = false, $autodate = true)
    {
        $this->categories = serialize($this->categories);
        $this->products = serialize($this->products);
        $this->manufacturers = serialize($this->manufacturers);

        return (int)$this->id > 0 ? $this->update($null_values) : $this->add($autodate, $null_values);
    }

    /**
     * @param $id_anmenu
     * @param null $id_lang
     * @param bool $active
     * @return mixed
     */
    public static function getList($id_anmenu, $id_lang = null, $active = true)
    {
        $id_lang = is_null($id_lang) ? Context::getContext()->language->id : $id_lang;

        $query = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'andropdown` d
            LEFT JOIN `' . _DB_PREFIX_ . 'andropdown_lang` dl ON d.`id_andropdown` = dl.`id_andropdown`
            WHERE d.`id_anmenu` = ' . (int)$id_anmenu . '
            AND `id_lang` = ' . (int)$id_lang . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            GROUP BY d.`id_andropdown`
            ORDER BY d.`position` ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        return $result;
    }

    /**
     * @return int
     */
    public function getMaxPosition()
    {
        $query = 'SELECT MAX(d.`position`)
            FROM `' . _DB_PREFIX_ . 'andropdown` d
            WHERE d.`id_anmenu` = ' . (int)$this->id_anmenu;

        return (int)Db::getInstance()->getValue($query);
    }

    /**
     * @param $id_andropdown
     * @param $position
     */
    public static function updatePosition($id_andropdown, $position)
    {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'andropdown`
			SET `position` = ' . (int)$position . '
			WHERE `id_andropdown` = ' . (int)$id_andropdown;

        Db::getInstance()->execute($query);
    }

    /**
     * @param null $id_lang
     * @return array
     */
    public function getProductsAutocompleteInfo($id_lang = null)
    {
        $id_lang = is_null($id_lang) ? Context::getContext()->language->id : $id_lang;

        $products = array();

        if (!empty($this->products)) {
            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'SELECT p.`id_product`, p.`reference`, pl.name
                FROM `' . _DB_PREFIX_ . 'product` p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = ' .
                (int)$id_lang . ')
                WHERE p.`id_product` IN (' . implode(array_map('intval', $this->products), ',') . ')'
            );

            foreach ($rows as $row) {
                $products[$row['id_product']] = trim($row['name']) . (!empty($row['reference']) ?
                        ' (ref: ' . $row['reference'] . ')' : '');
            }
        }

        return $products;
    }

    /**
     * @param null $array_category_id
     * @param null $id_lang
     * @return bool
     */
    public static function getCategoriesByArrayId($array_category_id = null, $id_lang = null)
    {
        if (empty($array_category_id)) {
            return false;
        }

        $context = Context::getContext();
        $id_lang = is_null($id_lang) ? $context->language->id : $id_lang;

        $query = 'SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = ' . (int)$id_lang . ' ' . Shop::addSqlRestrictionOnLang('cl') . ')
            WHERE c.`id_category` IN (' . implode(array_map('intval', $array_category_id), ',') . ')
            AND c.`active` = 1
            GROUP BY c.`id_category`
            ORDER BY `level_depth` ASC, category_shop.`position` ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$result) {
            return false;
        }

        foreach ($result as &$row) {
            $row['url'] = $context->link->getCategoryLink(
                $row['id_category'],
                $row['link_rewrite']
            );
        }

        return $result;
    }

    /**
     * @param null $array_product_id
     * @param null $id_lang
     * @return bool
     */
    public static function getProductsByArrayId($array_product_id = null, $id_lang = null)
    {
        if (empty($array_product_id)) {
            return false;
        }

        $context = Context::getContext();
        $id_lang = is_null($id_lang) ? $context->language->id : $id_lang;

        $sql = new DbQuery();
        $sql->select(
            'p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`,
            pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, 
            pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image, il.`legend`, 
            m.`name` AS manufacturer_name,
            DATEDIFF(
                product_shop.`date_add`,
                DATE_SUB(
                    NOW(),
                    INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ?
                Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
                )
            ) > 0 AS new'
        );

        $sql->from('product', 'p');
        $sql->join(Shop::addSqlAssociation('product', 'p'));
        $sql->leftJoin('product_lang', 'pl', 'p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl'));
        $sql->leftJoin('image', 'i', 'i.`id_product` = p.`id_product`');
        $sql->join(Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1'));
        $sql->leftJoin('image_lang', 'il', 'i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang);
        $sql->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

        if (is_array($array_product_id) && count($array_product_id) > 0){
           $sql->where('p.`id_product` IN (' . implode(',', array_map('intval', $array_product_id)) . ')');
        }

        $sql->groupBy('product_shop.id_product');

        if (Combination::isFeatureActive()) {
            $sql->select('MAX(product_attribute_shop.id_product_attribute) id_product_attribute');
            $sql->leftOuterJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`');
            $sql->join(Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on = 1'));
        }
        $sql->join(Product::sqlStock('p', Combination::isFeatureActive() ? 'product_attribute_shop' : 0));

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return false;
        }

        return Product::getProductsProperties((int)$id_lang, $result);
    }

    /**
     * @param null $array_manufacturer_id
     * @param null $id_lang
     * @return bool
     */
    public static function getManufacturersByArrayId($array_manufacturer_id = null, $id_lang = null)
    {
        if (empty($array_manufacturer_id)) {
            return false;
        }

        $context = Context::getContext();
        $id_lang = is_null($id_lang) ? $context->language->id : $id_lang;

        $query = 'SELECT m.*, ml.`description`, ml.`short_description`
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            ' . Shop::addSqlAssociation('manufacturer', 'm') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer_lang` ml ON (m.`id_manufacturer` = ml.`id_manufacturer` AND ml.`id_lang` = ' . (int)$id_lang . ')
            WHERE m.`id_manufacturer` IN (' . (version_compare(phpversion(), '8.0', '<') ? implode(array_map('intval', $array_manufacturer_id), ',') : implode(',', array_map('intval', $array_manufacturer_id))) . ')
            AND m.`active` = 1
            GROUP BY m.`id_manufacturer`
            ORDER BY m.`name`';

        $manufacturers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$manufacturers) {
            return false;
        }

        foreach ($manufacturers as &$item) {
            $item['image'] = $context->link->getManufacturerImageLink($item['id_manufacturer']);
            $item['url'] = $context->link->getManufacturerLink($item['id_manufacturer']);
        }

        return $manufacturers;
    }

    /**
     * @param $id_category
     * @param null $id_lang
     * @return bool
     */
    public static function getSubCategories($id_category, $id_lang = null)
    {
        $context = Context::getContext();
        $id_lang = is_null($id_lang) ? $context->language->id : $id_lang;

        $sql_groups_where = '';
        $sql_groups_join = '';
        if (Group::isFeatureActive()) {
            $sql_groups_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cg.`id_category` = c.`id_category`)';
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups_where = 'AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int)Group::getCurrent()->id);
        }

        $query = 'SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = ' . (int)$id_lang . ' ' . Shop::addSqlRestrictionOnLang('cl') . ')
            ' . $sql_groups_join . '
            WHERE `id_parent` = ' . (int)$id_category . '
            AND c.`active` = 1
            ' . $sql_groups_where . '
            GROUP BY c.`id_category`
            ORDER BY `level_depth` ASC, category_shop.`position` ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$result) {
            return false;
        }

        foreach ($result as &$row) {
            $row['url'] = $context->link->getCategoryLink(
                $row['id_category'],
                $row['link_rewrite']
            );
        }

        return $result;
    }
}
