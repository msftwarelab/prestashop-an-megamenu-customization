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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'product_autocomplete'}	
	<div>
		<input type="text" id="products_autocomplete" autocomplete="off" size="42" />
		<div id="products_autocompleteDiv" style="font-size: 1.1em; margin-top: 10px; margin-left: 10px;">
		{if isset($fields_value.products)}
			{foreach $fields_value.products as $id => $name}
			<p id="product-{$id|escape:'htmlall':'UTF-8'}">#{$id|escape:'htmlall':'UTF-8'} - {$name|escape:'htmlall':'UTF-8'} <span style="cursor: pointer;" onclick="$(this).parent().remove();"><img src="../img/admin/delete.gif" /></span><input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}[]" value="{$id|escape:'htmlall':'UTF-8'}" /></p>
			{/foreach}
		{/if}
		</div>
		<script type="text/javascript">
			function autocompleteProduct() {
				$("#products_autocomplete").autocomplete("{$input.ajax_path}", {
					minChars: 1,
					autoFill: true,
					max:20,
					matchContains: true,
					mustMatch:true,
					scroll:false,
					cacheLength:0,
					formatItem: function(item) {
					    var i, text = '';
					    for (i = 0; i <= item.length - 2; ++i) {
					        text+=item[i];
                        }
					    return "#"+item[item.length - 1]+" - "+text;
					}

				}).result(function(event, data, formatted){
					if (data == null)
						return false;
                    var productId,
                        productName = '';
                    for (i = 0; i <= data.length - 2; ++i) {
                        productName+=data[i];
                    }
					productId = data[data.length - 1];

					$("#product-" + productId).remove();
					html = html_aclist.replace(/xproductIdy/g,productId).replace(/xproductNamey/g,productName);
					$("#products_autocompleteDiv").append(html);
					
					$(this).val("");
				});
			}
			var html_aclist = '<p id="product-xproductIdy">#xproductIdy - xproductNamey <span style="cursor: pointer;" onclick="$(this).parent().remove();"><img src="../img/admin/delete.gif" /></span><input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}[]" value="xproductIdy" /></p>';
			
			$(document).ready(function(){
				autocompleteProduct();
			});
		</script>
	</div>
	{elseif $input.type == 'color'}
		<div class="form-group">
			<div class="col-lg-6">
				<div class="input-group">
					<input type="color"
					data-hex="true"
					{if isset($input.class)} class="{$input.class}"
					{else} class="color mColorPickerInput"{/if}
					name="{$input.name|escape:'htmlall':'UTF-8'}"
					value="{$fields_value[$input.name]|escape:'html':'UTF-8'}" />
				</div>
			</div>
		</div>
	
	{elseif $input.type == 'manufacturer'}
	<div id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}">
		{foreach from=$input.list_manufacturer key=manu_id item=manu_name}
		<div class="checkbox">
			<label>
				<input type="checkbox" name="{$input.name|escape:'htmlall':'UTF-8'}[]" value="{$manu_id|escape:'html':'UTF-8'}" {if $manu_id|in_array:$fields_value.manufacturers}checked="checked"{/if} >
				{$manu_name|escape:'html':'UTF-8'}
			</label>
		</div>
		{/foreach}
	</div>
	{else}
		{$smarty.block.parent}
    {/if}

{/block}
