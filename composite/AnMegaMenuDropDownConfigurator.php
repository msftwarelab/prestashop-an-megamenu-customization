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

class AnMegaMenuDropDownConfigurator extends an_megamenu
{
    /**
     * @return mixed
     */
    protected function processSaveDropdown()
    {
        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $id_andropdown = (int)Tools::getValue('id_andropdown');
        $andropdown = new AnDropdown($id_anmenu);
        if ($id_andropdown) {
            $andropdown = new AnDropdown($id_anmenu, $id_andropdown);
        }

        $andropdown->active = (int)Tools::getValue('active');
        $andropdown->position = (int)Tools::getValue('position');
        $andropdown->column = Tools::getValue('column');
        $andropdown->content_type = Tools::getValue('content_type');
        $andropdown->categories = Tools::getValue('categories', array());
        $andropdown->products = Tools::getValue('products', array());
        $andropdown->manufacturers = Tools::getValue('manufacturers', array());
    //    $andropdown->title = Tools::getValue('title', '');

        if (isset($_FILES['drop_bgimage']) && isset($_FILES['drop_bgimage']['tmp_name']) && !empty($_FILES['drop_bgimage']['tmp_name'])) {
            $error = ImageManager::validateUpload(
                $_FILES['drop_bgimage'],
                Tools::convertBytes(ini_get('upload_max_filesize'))
            );
            if ($error) {
                $this->html .= $this->displayError($error);
            } else {
                $move_file = move_uploaded_file(
                    $_FILES['drop_bgimage']['tmp_name'],
                    $this->local_path . $this->bg_img_folder . $_FILES['drop_bgimage']['name']
                );
                if ($move_file) {
                    $andropdown->drop_bgimage = $_FILES['drop_bgimage']['name'];
                } else {
                    $this->html .= $this->displayError($this->tr('File upload error.'));
                }
            }
        }

        $languages = Language::getLanguages(false);
        $id_lang_default = (int)$this->context->language->id;
        $static_content = array();
        $title = array();
        foreach ($languages as $lang) {
            $static_content[$lang['id_lang']] = Tools::getValue('static_content_' . $lang['id_lang']);
            if (!$static_content[$lang['id_lang']]) {
                $static_content[$lang['id_lang']] = Tools::getValue('static_content_' . $id_lang_default);
            }
			
            $title[$lang['id_lang']] = Tools::getValue('title_' . $lang['id_lang']);
            if (!$title[$lang['id_lang']]) {
                $title[$lang['id_lang']] = Tools::getValue('title_' . $id_lang_default);
            }		 
			
        }

		$andropdown->title = $title;
        $andropdown->static_content = $static_content;


        $result = $andropdown->validateFields(false);
        if ($result) {
            $andropdown->save();

            if ($id_andropdown) {
                $this->html .= $this->displayConfirmation($this->tr('Dropdown Content has been updated.'));
            } else {
                $this->html .= $this->displayConfirmation($this->tr('Dropdown Content has been created successfully.'));
            }

            $this->_clearCache('*');
            return $andropdown->id;
        } else {
            $this->html .= $this->displayError($this->tr('An error occurred while attempting to save Dropdown Content.'));
        }

        return false;
    }

    /**
     * @return string
     */
    protected function renderDropdownList()
    {
        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $anmenu = new AnMenu($id_anmenu);
        if ((int)$anmenu->drop_column < 1) {
            $msg = $this->tr('You have to ENABLE the "Dropdown Menu Columns" option.');
            if (!$id_anmenu) {
                $msg = $this->tr('You have to SAVE this menu before adding a dropdown content.');
            }
            $dropdown_title = $this->tr('Dropdown Contents');
            $result = '
                <div class="panel col-lg-12">
                    <div class="panel-heading">' . $dropdown_title . '</div>
                    <div class="table-responsive-row clearfix">' . $msg . '</div>
                </div>';

            return $result;
        }

        $andropdowns = AnDropdown::getList($id_anmenu, (int)$this->context->language->id, false);

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->toolbar_btn['new'] = array(
            'href' => $this->currentIndex . '&addandropdown&id_anmenu=' . $id_anmenu . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->tr('Add New'),
        );
        $helper->simple_header = false;
        $helper->listTotal = count($andropdowns);
        $helper->identifier = 'id_andropdown';
        $helper->table = 'andropdown';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->no_link = true;
        $helper->module = $this;
        $helper->title = $this->tr(
            'Dropdown Contents',
            array(),
            'Modules.AnMegaMenu.Admin'
        );
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->currentIndex . '&id_anmenu=' . $id_anmenu;
        $helper->position_identifier = 'andropdown';
        $helper->position_group_identifier = $id_anmenu;

        $helper->toolbar_btn['back'] = array(
            'href' => $this->currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->tr(
                'Back to Menu list',
                array(),
                'Modules.AnMegaMenu.Admin'
            )
        );
		
        $this->context->smarty->assign(array(
            'modulePath' => $this->_path,
			'configure' => $this->context->link->getAdminLink('AdminModules').'&configure=an_megamenu',
			'sitemap' => $this->context->link->getPageLink('sitemap')
        ));			

        return $this->display(_PS_MODULE_DIR_.'/an_megamenu/', 'views/templates/admin/config_top.tpl').$helper->generateList($andropdowns, $this->getDropdownList());
    }

    /**
     * @return array
     */
    protected function getDropdownList()
    {
        $fields_list = array(
            'id_andropdown' => array(
                'title' => $this->tr('ID'),
                'align' => 'center',
                //'class' => 'fixed-width-xs',
                'orderby' => false,
                'search' => false,
                'type' => 'zid_dropdown',
            ),
            'content_type' => array(
                'title' => $this->tr('Content Type'),
                'orderby' => false,
                'search' => false,
                'type' => 'andropdowntype',
            ),
            'position' => array(
                'title' => $this->tr('Position'),
                'align' => 'center',
                'orderby' => false,
                'search' => false,
                //'class' => 'fixed-width-md',
                'position' => true,
                'type' => 'zposition',
            ),
            'active' => array(
                'title' => $this->tr('Status'),
                'active' => 'status',
                'type' => 'bool',
                //'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false,
                'search' => false,
            ),
        );

        return $fields_list;
    }

    /**
     * @return mixed
     */
    protected function renderDropdownForm()
    {
        $id_anmenu = (int)Tools::getValue('id_anmenu');

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveandropdown';
        $helper->currentIndex = $this->currentIndex . '&id_anmenu=' . $id_anmenu;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getDropdownFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $form = $helper->generateForm(array($this->getDropdownForm()));

        Context::getContext()->smarty->assign('token', Tools::getAdminTokenLite('AdminModules'));
		
        $this->context->smarty->assign(array(
            'modulePath' => $this->_path,
			'configure' => $this->context->link->getAdminLink('AdminModules').'&configure=an_megamenu',
			'sitemap' => $this->context->link->getPageLink('sitemap')
        ));		

        return $this->display(_PS_MODULE_DIR_.'/an_megamenu/', 'views/templates/admin/config_top.tpl').$form;
    }

    /**
     * @return array
     */
    protected function getDropdownForm()
    {
        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $anmenu = new AnMenu($id_anmenu, (int)$this->context->language->id);
        $id_andropdown = (int)Tools::getValue('id_andropdown');
        $andropdown = new AnDropdown($id_anmenu, $id_andropdown, (int)$this->context->language->id);
        $root = Category::getRootCategory();

        $legent_title = $anmenu->name . ' > ' . $this->tr('Add New Dropdown Content');
        if ($id_andropdown) {
            $legent_title = $anmenu->name . ' > ' . $this->tr('Edit Dropdown Content');
        }

        $list_columns = array();
        for ($i = 1; $i <= $anmenu->drop_column; ++$i) {
            $list_columns[$i]['id'] = 'content_' . $i . '_column';
            $list_columns[$i]['value'] = $i;
            $list_columns[$i]['label'] = $i . ($i == 1 ? $this->tr('col') : $this->tr('cols'));
        }

        $content_type_options = array(
            'query' => array(
                array('id' => 'none', 'name' => ''),
                array('id' => 'category', 'name' => $this->tr('Category')),
                array('id' => 'product', 'name' => $this->tr('Product')),
                array('id' => 'html', 'name' => $this->tr('Custom HTML')),
                array('id' => 'manufacturer', 'name' => $this->tr('Manufacturer')),
            ),
            'id' => 'id',
            'name' => 'name',
        );

        $manufacturers = Manufacturer::getManufacturers();
        $list_manufacturer = array();
        if ($manufacturers) {
            foreach ($manufacturers as $manufacturer) {
                $list_manufacturer[$manufacturer['id_manufacturer']] = $manufacturer['name'];
            }
        }

        $image_url = false;
        $image_size = false;
        if ($id_andropdown) {
            if ($andropdown->drop_bgimage) {
                $image_url = $this->_path . $this->bg_img_folder . $andropdown->drop_bgimage;
                $image_size = filesize($this->local_path . $this->bg_img_folder . $andropdown->drop_bgimage) / 1000;
            }
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $legent_title,
                    'icon' => 'icon-book',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_andropdown',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->tr('Status'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->tr('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->tr('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'position',
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->tr('Content Columns'),
                        'name' => 'column',
                        'values' => $list_columns,
                        'hint' => $this->tr('The number of columns of dropdown content. Maximum value is "Dropdown Menu Columns"'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->tr('Content Type'),
                        'name' => 'content_type',
                        'id' => 'content_type_selectbox',
                        'options' => $content_type_options,
                        'hint' => $this->tr('Dropdown Content Type.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Title',
                        'name' => 'title',
						'lang' => true,
                    ),
                    array(
                        'type' => 'categories',
                        'label' => $this->tr('Select the Parent Categories'),
                        'name' => 'categories',
                        'hint' => $this->tr('Dropdown content will display the subcategories of this Parent Categories'),
                        'tree' => array(
                            'use_search' => false,
                            'id' => 'categoryBox',
                            'root_category' => $root->id,
                            'use_checkbox' => true,
                            'selected_categories' => $andropdown->categories,
                        ),
                        'form_group_class' => 'content_type_category',
                    ),
                    array(
                        'type' => 'product_autocomplete',
                        'label' => $this->tr('Select the Products'),
                        'name' => 'products',
                        'ajax_path' => $this->currentIndex . '&ajax=1&ajaxProductsList&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'hint' => $this->tr('Begin typing the First Letters of the Product Name, then select the Product from the Drop-down List.'),
                        'form_group_class' => 'content_type_product',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->tr('Custom HTML Content'),
                        'name' => 'static_content',
                        'autoload_rte' => true,
                        'lang' => true,
                        'rows' => 10,
                        'cols' => 100,
                        'form_group_class' => 'content_type_html',
                    ),
                    array(
                        'type' => 'manufacturer',
                        'label' => $this->tr('Select the Manufacturers'),
                        'name' => 'manufacturers',
                        'list_manufacturer' => $list_manufacturer,
                        'form_group_class' => 'content_type_manufacturer',
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->tr('Dropdown Background Image'),
                        'name' => 'drop_bgimage',
                        'desc' => $this->tr('Upload a new background image for dropdown menu from your computer'),
                        'display_image' => true,
                        'image' => $image_url ? '<img src="' . $image_url . '" alt="" class="img-thumbnail" style="max-width:410px;" />' : false,
                        'size' => $image_size,
                        'delete_url' => $this->currentIndex . '&token='
                            . Tools::getAdminTokenLite('AdminModules')
                            . '&deleteBackgroundDDImage' . '&id_anmenu=' . $id_anmenu
                            . '&id_andropdown=' . $id_andropdown
                            .'&updateandropdown',
                    ),
                ),
                'submit' => array(
                    'title' => $this->tr('Save and Stay'),
                ),
                'buttons' => array(
                    array(
                        'href' => $this->currentIndex . '&viewanmenu&id_anmenu=' . $id_anmenu . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->tr('Cancel'),
                        'icon' => 'process-icon-cancel',
                    ),
                ),
            ),
        );

        return $fields_form;
    }

    /**
     * @return array
     */
    protected function getDropdownFieldsValues()
    {
        $fields_value = array();

        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $id_andropdown = (int)Tools::getValue('id_andropdown');
        $andropdown = new AnDropdown($id_anmenu, $id_andropdown);

        $fields_value['id_andropdown'] = $id_andropdown;
        $fields_value['active'] = Tools::getValue('active', $andropdown->active);
        $fields_value['position'] = Tools::getValue('position', $andropdown->position);
        $fields_value['column'] = Tools::getValue('column', $andropdown->column);
        $fields_value['content_type'] = Tools::getValue('content_type', $andropdown->content_type);
        $fields_value['products'] = $andropdown->getProductsAutocompleteInfo($this->context->language->id);
        $fields_value['manufacturers'] = $andropdown->manufacturers;
        $fields_value['drop_bgimage'] = Tools::getValue('drop_bgimage', $andropdown->drop_bgimage);
        $fields_value['title'] = Tools::getValue('title', $andropdown->title);

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $default_static_content = isset($andropdown->static_content[$lang['id_lang']]) ? $andropdown->static_content[$lang['id_lang']] : '';
            $fields_value['static_content'][$lang['id_lang']] = Tools::getValue('static_content_' . (int)$lang['id_lang'], $default_static_content);
			
			
             $default_title = isset($andropdown->title[$lang['id_lang']]) ? $andropdown->title[$lang['id_lang']] : '';
            $fields_value['title'][$lang['id_lang']] = Tools::getValue('title_' . (int)$lang['id_lang'], $default_title);	 
        }

        return $fields_value;
    }
}
