/**
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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

if(typeof aObjToString !== 'function'){
	window.aObjToString = function(obj) {
		var str = '';
		for (var p in obj) {
			if (obj.hasOwnProperty(p)) {
				str += p + '=' + obj[p] + '&';
			}
		}
		return str;
	}
};

if(typeof aInitTableDnD !== 'function'){
	window.aInitTableDnD = function(module_name, table_class) {
		if (typeof(module_name) == 'undefined' || typeof(table_class) == 'undefined')
			return;

		table = 'table.' + table_class;
		$(table).tableDnD({
			onDragStart: function(table, row) {
				originalOrder = $.tableDnD.serialize();
				reOrder = ':even';
				if (table.tBodies[0].rows[1] && $('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
					reOrder = ':odd';
				$(table).find('#' + row.id).parent('tr').addClass('myDragClass');
			},
			dragHandle: 'dragHandle',
			onDragClass: 'myDragClass',
			onDrop: function(table, row) {
				if (originalOrder != $.tableDnD.serialize()) {
					var way = (originalOrder.indexOf(row.id) < $.tableDnD.serialize().indexOf(row.id))? 1 : 0;
					var ids = row.id.split('_');
					var tableDrag = table;
					var params = '';
					table.id = table.id.replace('table-', '');
					params = {
						updatePositions: table_class,
						configure: module_name,
						//id_group : ids[1],
						id : ids[2],
						way: way,
						ajax: 1
					}

					$.ajax({
						type: 'POST',
						headers: { "cache-control": "no-cache" },
						async: false,
						url: currentIndex + '&token=' + token + '&' + 'rand=' + new Date().getTime(),
						data: $.tableDnD.serialize() + '&' + aObjToString(params) ,
						success: function(data) {
							var nodrag_lines = $(tableDrag).find('tr:not(".nodrag")');
							
							var reg = /_[0-9]$/g;
							nodrag_lines.each(function(i) {
								$(this).attr('id', $(this).attr('id').replace(reg, '_' + (i+1)));
								$(this).find('.positions').text(i+1);
							});

							nodrag_lines.removeClass('odd');
							nodrag_lines.filter(':odd').addClass('odd');
							nodrag_lines.children('td.dragHandle').find('a').attr('disabled',false);

							nodrag_lines.children('td.dragHandle:first').find('a:even').attr('disabled',true);
							nodrag_lines.children('td.dragHandle:last').find('a:odd').attr('disabled',true);

							showSuccessMessage(update_success_msg);
						}
					});
				}
			}
		});
	}
};