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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;

class AnMegaMenuConfigurator extends an_megamenu
{
    /**
     * Generate configuration form
     *
     * @return string
     */
    public function getConfiguration()
    {
        $this->context->controller->addJqueryPlugin('tablednd');
        $this->context->controller->addJS($this->_path . 'views/js/position.js');
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');

        if (Tools::isSubmit('saveanmenu')) {
            $id_anmenu = $this->processSaveMenu();
            if ($id_anmenu === false) {
                return $this->displayError($this->tr('An error occurred while attempting to save Menu.')) . $this->renderMenuForm();
            }
            Tools::redirectAdmin($this->currentIndex . '&id_anmenu=' . $id_anmenu . '&updateanmenu&conf=4&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('addanmenu') || Tools::isSubmit('updateanmenu') || Tools::isSubmit('viewanmenu')) {
            return $this->renderMenuForm();
        } elseif (Tools::isSubmit('deleteBackgroundImage')) {
            $id_anmenu = (int)Tools::getValue('id_anmenu');
            $anmenu = new AnMenu($id_anmenu);
            if ($anmenu->drop_bgimage) {
                $image_path = $this->local_path . $this->bg_img_folder . $anmenu->drop_bgimage;

                if (file_exists($image_path)) {
                    unlink($image_path);
                }

                $anmenu->drop_bgimage = null;
                $anmenu->update(false);
                $this->_clearCache('*');
            }

            Tools::redirectAdmin($this->currentIndex . '&id_anmenu=' . $id_anmenu . '&updateanmenu&token=' . Tools::getAdminTokenLite('AdminModules') . '&conf=7');
        } elseif (Tools::isSubmit('deleteanmenu')) {
            $anmenu = new AnMenu((int)Tools::getValue('id_anmenu'));
            $anmenu->delete();
            $this->_clearCache('*');
            Tools::redirectAdmin($this->currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('statusanmenu')) {
            $this->ajaxStatusMenu();
        } elseif (Tools::getValue('updatePositions') == 'anmenu') {
            $this->ajaxPositionsMenu();
        } elseif (Tools::isSubmit('saveandropdown')) {
            $id_anmenu = (int)Tools::getValue('id_anmenu');
            $id_andropdown = $this->processSaveDropdown();
            if ($id_andropdown === false) {
                return $this->displayError($this->tr('An error occurred while attempting to save Dropdown Content.')) . $this->renderDropdownForm();
            }
            Tools::redirectAdmin($this->currentIndex . '&id_anmenu=' . $id_anmenu . '&id_andropdown=' . $id_andropdown . '&updateandropdown&conf=4&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('deleteBackgroundDDImage')) {
            $id_anmenu = (int)Tools::getValue('id_anmenu');
            $id_andropdown = (int)Tools::getValue('id_andropdown');
            if(!$id_andropdown ) {
                return $this->renderDropdownForm();
            }
            $andropdown = new AnDropdown($id_anmenu, $id_andropdown);
            if ($andropdown->drop_bgimage) {
                $image_path = $this->local_path . $this->bg_img_folder . $andropdown->drop_bgimage;

                if (file_exists($image_path)) {
                    unlink($image_path);
                }

                $andropdown->drop_bgimage = null;
                $andropdown->update(false);
                //$this->_clearCache('*');
            }
            Tools::redirectAdmin(
                $this->currentIndex . '&id_anmenu=' . $id_anmenu . '&id_andropdown=' . $id_andropdown . '&updateandropdown&token=' . Tools::getAdminTokenLite('AdminModules')
            );
        } elseif (Tools::isSubmit('addandropdown') || Tools::isSubmit('updateandropdown')) {
            return $this->renderDropdownForm();
        } elseif (Tools::isSubmit('deleteandropdown')) {
            $id_anmenu = (int)Tools::getValue('id_anmenu');
            $id_andropdown = (int)Tools::getValue('id_andropdown');
            $andropdown = new AnDropdown($id_anmenu, $id_andropdown);
            $andropdown->delete();
            $this->_clearCache('*');
            Tools::redirectAdmin($this->currentIndex . '&viewanmenu&id_anmenu=' . $id_anmenu . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('statusandropdown')) {
            $this->ajaxStatusDropdown();
        } elseif (Tools::getValue('updatePositions') == 'andropdown') {
            $this->ajaxPositionsDropdown();
        } elseif (Tools::isSubmit('listandropdown')) {
            return $this->renderDropdownList();
        } elseif (Tools::isSubmit('ajaxProductsList')) {
            $this->ajaxProductsList();
        } else {
            return $this->renderMenuList();
        }
        return '';
    }

    /**
     * Pre processor
     */
    public function preProcess()
    {
        $id_lang = (int)$this->context->language->id;
        $home_cat = array(Configuration::get('PS_HOME_CATEGORY'), Configuration::get('PS_ROOT_CATEGORY'));
        $anmenus = AnMenu::getList($id_lang);

        foreach ($anmenus as &$menu) {
            $andropdowns = AnDropdown::getList($menu['id_anmenu'], $id_lang);

            foreach ($andropdowns as &$dropdown) {
                if ($dropdown['content_type'] == 'category') {
                    $array_ids = Tools::unSerialize($dropdown['categories']);
                    $dropdown['categoriesHtml'] = $this->renderCategoriesTreeHtml($array_ids, $id_lang);
                } elseif ($dropdown['content_type'] == 'product') {
                    $array_ids = Tools::unSerialize($dropdown['products']);
                    $dropdown['products'] = false;
                    $products = AnDropdown::getProductsByArrayId($array_ids, $id_lang);

                    if ($products) {
                        $present_products = array();
                        $assembler = new ProductAssembler($this->context);

                        $presenterFactory = new ProductPresenterFactory($this->context);
                        $presentationSettings = $presenterFactory->getPresentationSettings();
                        $presenter = new ProductListingPresenter(
                            new ImageRetriever($this->context->link),
                            $this->context->link,
                            new PriceFormatter(),
                            new ProductColorsRetriever(),
                            $this->context->getTranslator()
                        );

                        foreach ($products as $rawProduct) {
                            $present_products[] = $presenter->present(
                                $presentationSettings,
                                $assembler->assembleProduct($rawProduct),
                                $this->context->language
                            );
                        }

                        $dropdown['products'] = $present_products;
                    }
                } elseif ($dropdown['content_type'] == 'manufacturer') {
                    $array_ids = Tools::unSerialize($dropdown['manufacturers']);
                    $dropdown['manufacturers'] = AnDropdown::getManufacturersByArrayId($array_ids, $id_lang);
                }
            }

            $menu['dropdowns'] = $andropdowns;

            if ($menu['drop_bgcolor'] == '#ffffff') {
                $menu['drop_bgcolor'] = false;
            }
            if ($menu['label_color'] == '#17cf00') {
                $menu['label_color'] = false;
            }
            if ($menu['bgimage_position']) {
                $position = explode(' ', $menu['bgimage_position']);
                $menu['position'] = '';
                if ($position[0] != 'center') {
                    $menu['position'] .= $position[0] . ': ' . $menu['position_x'] . 'px;';
                }
                if ($position[1] != 'center') {
                    $menu['position'] .= $position[1] . ': ' . $menu['position_y'] . 'px;';
                }
            }
        }

        $this->context->smarty->assign(array(
            'anmenus' => $anmenus,
            'bg_image_url' => $this->_path . $this->bg_img_folder,
            'is_rtl' => $this->context->language->is_rtl,
			'cart' => (new CartPresenter())->present(isset($params['cart']) ? $params['cart'] : $this->context->cart),
        ));
    }
	
    public static function categoryIsActive($idCategory)
    {
        $row = Db::getInstance()->getRow('
		SELECT `id_category`
		FROM ' . _DB_PREFIX_ . 'category c
		WHERE c.`active` = 1 AND c.`id_category` = ' . (int) $idCategory);

        return isset($row['id_category']);
    }	

	public function renderCategoriesTreeHtml(array $array_ids, $id_lang, $categories = null, $level = 1)
	{
		$html = '';
		
		
/* 		$test = CategoryCore::getCategories($this->context->language->id);
		echo '<pre>';var_dump($test);  */
		
		if ($categories === null) {
			$helper = new HelperTreeCategories($this->name, null, null, $this->context->language->id);
			$categories = $helper->getData();
			
	/* 		echo '<pre>';var_dump($categories); die;  */
		}

		foreach ($categories as $category) {
			$hasChildren = !empty($category['children']);
			if (in_array($category['id_category'], $array_ids)) {
				$childHtml = $hasChildren ? $this->renderCategoriesTreeHtml($array_ids, $id_lang, $category['children'], $level + 1) : '';
				$href = $this->context->link->getCategoryLink($category['id_category']);
				if (AnMegaMenuConfigurator::categoryIsActive($category['id_category'])){
					$html .= '
						<div class="category-item level-' . $level . '">
							<p class="h5 category-title"><a href="' . $href . '" title="' . $category['name'] . '">' . $category['name'] . '</a></p>
							' . $childHtml . '
						</div>
					';
				}
			} else if ($hasChildren) {
			//	echo AnMegaMenuConfigurator::categoryIsActive();
				$html .= $this->renderCategoriesTreeHtml($array_ids, $id_lang, $category['children'], $level);
			}
			
		}
		return $html;
	}
}
