{*
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

      {if !isset($an_width_on_mobile)}
      {$an_width_on_mobile = '768'}
    {/if}
    {if $anmenus}
    <div class="mobile-amega-menu">
    
    <div id="mobile-amegamenu">
      <ul class="anav-top anav-mobile">
      {foreach from=$anmenus item=menu}
      <li class="amenu-item mm-{$menu.id_anmenu} {if $menu.dropdowns}plex{/if}">
        <div class="{if $menu.dropdowns && $menu.drop_column}mobile_item_wrapper {else} mobile-item-nodropdown{/if}">
        {if $menu.link}<a href="{$menu.link}" class="amenu-link">{else}<span class="amenu-link">{/if}
          <span>{$menu.name}</span>
          {if $menu.label}<sup {if $menu.label_color}style="background-color: {$menu.label_color}; color: {$menu.label_color};"{/if}><span>{$menu.label}</span></sup>{/if}
          {if $menu.dropdowns}<span class="mobile-toggle-plus d-flex align-items-center justify-content-center"><i class="caret-down-icon"></i></span>{/if}
        {if $menu.link}</a>{else}</span>{/if}
            {if $menu.dropdowns && $menu.drop_column}
            <span class="mobile-toggler">
              <svg 
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              width="5px" height="9px">
             <path fill-rule="evenodd"  fill="rgb(0, 0, 0)"
              d="M4.913,4.292 L0.910,0.090 C0.853,0.030 0.787,-0.000 0.713,-0.000 C0.639,-0.000 0.572,0.030 0.515,0.090 L0.086,0.541 C0.029,0.601 -0.000,0.670 -0.000,0.749 C-0.000,0.827 0.029,0.896 0.086,0.956 L3.462,4.500 L0.086,8.044 C0.029,8.104 -0.000,8.173 -0.000,8.251 C-0.000,8.330 0.029,8.399 0.086,8.459 L0.515,8.910 C0.572,8.970 0.639,9.000 0.713,9.000 C0.787,9.000 0.853,8.970 0.910,8.910 L4.913,4.707 C4.970,4.647 4.999,4.578 4.999,4.500 C4.999,4.422 4.970,4.353 4.913,4.292 Z"/>
             </svg>
            </span>
            {/if}
        </div>
        {if $menu.dropdowns && $menu.drop_column}
        <div class="adropdown-mobile" {if $menu.drop_bgcolor}style="background-color: {$menu.drop_bgcolor};"{/if}>      
          {foreach from=$menu.dropdowns item=dropdown}
          {if $dropdown.content_type != 'none' && $dropdown.column}
          <div class="dropdown-content dd-{$dropdown.id_andropdown}">
            {if $dropdown.content_type == 'category'}
              {if $dropdown.categories}
                {$dropdown.categoriesHtml nofilter}
              {/if}
    
            {elseif $dropdown.content_type == 'product'}
              {if $dropdown.products}
              <div class="products-grid">
              {foreach from=$dropdown.products item=product name=products}
                <div class="product-item">
                  <p class="product-thumbnail"><a href="{$product.url}" title="{$product.name}"><img class="img-fluid" src="{$product.cover.bySize.menu_default.url}" alt="{$product.cover.legend}" /></a></p>
                  <div class="product-information-dropdown">
                    <h5 class="product-name"><a href="{$product.url}" title="{$product.name}">{$product.name|truncate:25:'...'}</a></h5>
                    {if $product.show_price}
                    <div class="product-price-and-shipping"><span class="price product-price">{$product.price}</span>
                    {if $product.has_discount}<span class="regular-price">{$product.regular_price}</span>{/if}</div>
                    {/if}
                  </div>
                </div>
              {/foreach}
              </div>
              {/if}
    
            {elseif $dropdown.content_type == 'manufacturer'}
              {if $dropdown.manufacturers}
              <div class="manufacturers-grid">
              {foreach from=$dropdown.manufacturers item=manufacturer name=manufacturers}
                <div class="manufacturer-item">
                  <div class="left-side"><div class="logo"><a href="{$manufacturer.url}" title=""><img class="img-fluid" src="{$manufacturer.image}" alt="" /></a></div></div>
                  <div class="middle-side"><a class="manufacturer-name" href="{$manufacturer.url}" title="">{$manufacturer.name}</a></div>
                </div>
              {/foreach}
              </div>
              {/if}
    
            {elseif $dropdown.content_type == 'html'}
              {if $dropdown.static_content}
              <div class="html-item typo">
                {$dropdown.static_content nofilter}
              </div>
              {/if}
            {/if}
          </div>
          {/if}
          {/foreach}
        </div>
        {/if}
      </li>
      {/foreach}
      </ul>
    </div>
    </div>
    {/if}
    

