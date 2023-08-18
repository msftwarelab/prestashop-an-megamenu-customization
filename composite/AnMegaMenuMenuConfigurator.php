<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AnMegaMenuMenuConfigurator extends an_megamenu
{
    /**
     * @return bool
     */
    protected function processSaveMenu()
    {
        $anmenu = new AnMenu();
        $id_anmenu = (int)Tools::getValue('id_anmenu');
        if ($id_anmenu) {
            $anmenu = new AnMenu($id_anmenu);
        }

        $anmenu->position = (int)Tools::getValue('position');
        $anmenu->active = (int)Tools::getValue('active');
        $anmenu->label_color = Tools::getValue('label_color');
        $anmenu->drop_column = Tools::getValue('drop_column');
        $anmenu->drop_bgcolor = Tools::getValue('drop_bgcolor');
        $anmenu->bgimage_position = Tools::getValue('bgimage_position');
        $anmenu->position_x = Tools::getValue('position_x');
        $anmenu->position_y = Tools::getValue('position_y');

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
                    $anmenu->drop_bgimage = $_FILES['drop_bgimage']['name'];
                } else {
                    $this->html .= $this->displayError($this->tr('File upload error.'));
                }
            }
        }

        $languages = Language::getLanguages(false);
        $id_lang_default = (int)$this->context->language->id;
        $name = array();
        $link = array();
        $label = array();
        foreach ($languages as $lang) {
            $name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
            if (!$name[$lang['id_lang']]) {
                $name[$lang['id_lang']] = Tools::getValue('name_' . $id_lang_default);
            }
            $link[$lang['id_lang']] = Tools::getValue('link_' . $lang['id_lang']);
            if (!$link[$lang['id_lang']]) {
                $link[$lang['id_lang']] = Tools::getValue('link_' . $id_lang_default);
            }
            $label[$lang['id_lang']] = Tools::getValue('label_' . $lang['id_lang']);
            if (!$label[$lang['id_lang']]) {
                $label[$lang['id_lang']] = Tools::getValue('label_' . $id_lang_default);
            }
        }
        $anmenu->name = $name;
        $anmenu->link = $link;
        $anmenu->label = $label;

        $result = $anmenu->validateFields(false) && $anmenu->validateFieldsLang(false);
        if ($result) {
            $anmenu->save();

            if ($id_anmenu) {
                $this->html .= $this->displayConfirmation($this->tr('Menu has been updated.'));
            } else {
                $this->html .= $this->displayConfirmation($this->tr('Menu has been created successfully.'));
            }

            $this->_clearCache('*');
            return $anmenu->id;
        } else {
            $this->html .= $this->displayError($this->tr('An error occurred while attempting to save Menu.'));
        }

        return false;
    }
	
	
	
    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Others'),
            ),
            'input' => array(

                array(
                    'type' => 'switch',
                    'label' => $this->l('Do not install samples at during reset the module'),
                    'name' => self::PREFIX.'do_not_install_samples',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_enabled_on',
                            'value' => 1
                        ),
                        array(
                            'id' => 'is_enabled_off',
                            'value' => 0
                        )
                    ),
                ),
				
				
				
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );	

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
		
        $paramList = $this->getParamList();

        foreach ($paramList as $key) {
            $helper->fields_value[self::PREFIX.$key] = $this->getParam($key);
        }

        return $helper->generateForm($fields_form);
    }	
	
    protected function getSubmitOutput()
    {
        $params = array();
        $paramList = $this->getParamList();

        foreach ($paramList as $key) {
            $params[$key] = Tools::getValue(self::PREFIX.$key);
        }

        foreach ($paramList as $key) {
            $this->updateParam($key, $params[$key]);
        }

        return $this->displayConfirmation($this->l('Settings updated'));
    }
	
	

    /**
     * @return mixed
     */
    protected function renderMenuList()
    {
        $anmenus = AnMenu::getList((int)$this->context->language->id, false);

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->toolbar_btn['new'] = array(
            'href' => $this->currentIndex . '&addanmenu&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->tr('Add New'),
        );
        $helper->simple_header = false;
        $helper->listTotal = count($anmenus);
        $helper->identifier = 'id_anmenu';
        $helper->table = 'anmenu';
        $helper->actions = array('view', 'edit', 'delete');
        $helper->show_toolbar = true;
        $helper->no_link = true;
        $helper->module = $this;
        $helper->title = $this->tr('Mega Menu Items');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->currentIndex;
        $helper->position_identifier = 'anmenu';
        $helper->position_group_identifier = 0;
		
        $this->context->smarty->assign(array(
            'modulePath' => $this->_path,
			'configure' => $this->context->link->getAdminLink('AdminModules').'&configure=an_megamenu',
			'sitemap' => $this->context->link->getPageLink('sitemap')
        ));
		
        if (Tools::isSubmit('submit'.$this->name)) {
            $output = $this->getSubmitOutput();
        }		
		
        return $this->display(_PS_MODULE_DIR_.'/an_megamenu/', 'views/templates/admin/config_top.tpl').$helper->generateList($anmenus, $this->getMenuList()).$this->displayForm();
    }

    /**
     * @return array
     */
    protected function getMenuList()
    {
        $fields_list = array(
            'id_anmenu' => array(
                'title' => $this->tr('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false,
                'search' => false,
                'type' => 'zid_menu',
            ),
            'name' => array(
                'title' => $this->tr('Title'),
                'orderby' => false,
                'search' => false,
                'type' => 'anmenu',
            ),
            'drop_column' => array(
                'title' => $this->tr('Type'),
                'orderby' => false,
                'search' => false,
                'type' => 'andropdowncolumn',
            ),
            'position' => array(
                'title' => $this->tr('Position'),
                'align' => 'center',
                'orderby' => false,
                'search' => false,
                'class' => 'fixed-width-md',
                'position' => true,
                'type' => 'zposition',
            ),
            'active' => array(
                'title' => $this->tr('Status'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false,
                'search' => false,
            ),
        );

        return $fields_list;
    }

    /**
     * @return string
     */
    protected function renderMenuForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveanmenu';
        $helper->currentIndex = $this->currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getMenuFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
		
        $this->context->smarty->assign(array(
            'modulePath' => $this->_path,
			'configure' => $this->context->link->getAdminLink('AdminModules').'&configure=an_megamenu',
			'sitemap' => $this->context->link->getPageLink('sitemap'),
        ));		

        if (Tools::isSubmit('viewanmenu')) {
            return '<div class="row"><div class="col-lg-12" id="dropdownList">' . $this->renderDropdownList() . '</div></div>';
        }

        return $this->display(_PS_MODULE_DIR_.'/an_megamenu/', 'views/templates/admin/config_top.tpl').'<div class="row"><div class="col-lg-12">' . $helper->generateForm(array($this->getMenuForm())) . '</div></div>';
    }

    /**
     * @return array
     */
    protected function getMenuForm()
    {
        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $anmenu = new AnMenu($id_anmenu, (int)$this->context->language->id);

        $legent_title = $this->tr('Add New Menu');
        if ($id_anmenu) {
            $legent_title = $anmenu->name;
        }

        $list_columns = array();
        $list_columns[0]['value'] = 0;
        $list_columns[0]['label'] = $this->tr('No Dropdown');
        for ($i = 1; $i < 6; ++$i) {
            $list_columns[$i]['value'] = $i;
            $list_columns[$i]['label'] = $i . ($i == 1 ? $this->tr(' column') : $this->tr(' columns'));
        }

        $list_positions = array(
            'query' => array(
                array('id' => 'left top', 'name' => $this->tr('left top')),
                array('id' => 'left center', 'name' => $this->tr('left center')),
                array('id' => 'left bottom', 'name' => $this->tr('left bottom')),
                array('id' => 'right top', 'name' => $this->tr('right top')),
                array('id' => 'right center', 'name' => $this->tr('right center')),
                array('id' => 'right bottom', 'name' => $this->tr('right bottom')),
                array('id' => 'center top', 'name' => $this->tr('center top')),
                array('id' => 'center center', 'name' => $this->tr('center center')),
                array('id' => 'center bottom', 'name' => $this->tr('center bottom')),
            ),
            'id' => 'id',
            'name' => 'name',
        );

        $image_url = false;
        $image_size = false;
        if ($id_anmenu) {
            if ($anmenu->drop_bgimage) {
                $image_url = $this->_path . $this->bg_img_folder . $anmenu->drop_bgimage;
                $image_size = filesize($this->local_path . $this->bg_img_folder . $anmenu->drop_bgimage) / 1000;
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
                        'name' => 'id_anmenu',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->tr('Title'),
                        'name' => 'name',
                        'lang' => true,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->tr('URL'),
                        'name' => 'link',
                        'lang' => true,
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
                        'type' => 'text',
                        'label' => $this->tr('Label'),
                        'name' => 'label',
                        'lang' => true,
                        'desc' => $this->tr('Label for this menu. E.g. SALE, NEW, HOT,...'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->tr('Label Background Color'),
                        'name' => 'label_color',
                        'lang' => true,
                        'desc' => $this->tr('Background color of Label. Default is #17cf00. Text color is white.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->tr('Menu Type'),
                        'name' => 'drop_column',
                        'options' => array(
                            'query' => $list_columns,
                            'id' => 'value',
                            'name' => 'label',
                        ),
                        'desc' => $this->tr('The number of columns of dropdown menu'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->tr('Dropdown Background Color'),
                        'name' => 'drop_bgcolor',
                        'desc' => $this->tr('The background color for dropdown menu'),
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->tr('Dropdown Background Image'),
                        'name' => 'drop_bgimage',
                        'desc' => $this->tr('Upload a new background image for dropdown menu from your computer'),
                        'display_image' => true,
                        'image' => $image_url ? '<img src="' . $image_url . '" alt="" class="img-thumbnail" style="max-width:410px;" />' : false,
                        'size' => $image_size,
                        'delete_url' => $this->currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&deleteBackgroundImage&id_anmenu=' . $id_anmenu,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->tr('Background Image Position'),
                        'name' => 'bgimage_position',
                        'options' => $list_positions,
                        'desc' => $this->tr('The starting position of a background image'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->tr('Position X'),
                        'name' => 'position_x',
                        'desc' => $this->tr('The horizontal position. Negative values are allowed.'),
                        'suffix' => 'px',
                        'col' => 5,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->tr('Position Y'),
                        'name' => 'position_y',
                        'desc' => $this->tr('The vertical position. Negative values are allowed.'),
                        'suffix' => 'px',
                        'col' => 5,
                    ),
                ),
                'submit' => array(
                    'title' => $this->tr('Save and Stay'),
                ),
                'buttons' => array(
                    array(
                        'href' => $this->currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->tr('Back'),
                        'icon' => 'process-icon-back',
                    ),
                ),
            ),
        );

        return $fields_form;
    }

    /**
     * @return array
     */
    protected function getMenuFieldsValues()
    {
        $fields_value = array();

        $id_anmenu = (int)Tools::getValue('id_anmenu');
        $anmenu = new AnMenu($id_anmenu);

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $default_name = isset($anmenu->name[$lang['id_lang']]) ? $anmenu->name[$lang['id_lang']] : '';
            $fields_value['name'][$lang['id_lang']] = Tools::getValue('name_' . (int)$lang['id_lang'], $default_name);
            $default_link = isset($anmenu->link[$lang['id_lang']]) ? $anmenu->link[$lang['id_lang']] : '';
            $fields_value['link'][$lang['id_lang']] = Tools::getValue('link_' . (int)$lang['id_lang'], $default_link);
            $default_label = isset($anmenu->label[$lang['id_lang']]) ? $anmenu->label[$lang['id_lang']] : '';
            $fields_value['label'][$lang['id_lang']] = Tools::getValue('label_' . (int)$lang['id_lang'], $default_label);
        }

        $fields_value['id_anmenu'] = $id_anmenu;
        $fields_value['active'] = Tools::getValue('active', $anmenu->active);
        $fields_value['position'] = Tools::getValue('position', $anmenu->position);
        $fields_value['label_color'] = Tools::getValue('label_color', $anmenu->label_color);
        $fields_value['drop_column'] = Tools::getValue('drop_column', $anmenu->drop_column);
        $fields_value['drop_bgcolor'] = Tools::getValue('drop_bgcolor', $anmenu->drop_bgcolor);
        $fields_value['drop_bgimage'] = Tools::getValue('drop_bgimage', $anmenu->drop_bgimage);
        $fields_value['bgimage_position'] = Tools::getValue('bgimage_position', $anmenu->bgimage_position);
        $fields_value['position_x'] = Tools::getValue('position_x', $anmenu->position_x);
        $fields_value['position_y'] = Tools::getValue('position_y', $anmenu->position_y);

        return $fields_value;
    }
}
