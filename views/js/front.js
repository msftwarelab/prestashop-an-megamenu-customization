/*js*/

function right_position() {
	$('#amegamenu .adropdown').each(function (index, element) {
		var this_offset_right = 0,
			container_offset_right = 0,
			this_offset_left = 0,
			container_offset_left = 0;
		this_offset_right = Math.floor($(this).offset().left + $(this).outerWidth(true));
		container_offset_right = Math.floor($(".container").offset().left + $(".container").outerWidth())-15;
		this_offset_left = Math.floor($(this).offset().left);
		container_offset_left = Math.floor($(".container").offset().left)+15;

		if (this_offset_left < container_offset_left ){
			$(this).offset({left: container_offset_left});
		}
		if (this_offset_right > container_offset_right){
			$(this).offset({left: (container_offset_right-$(this).outerWidth(true))});
		}

	});
}
function dropdown_scroll() {
	let windowHeight = $(window).height();
	$('#amegamenu .adropdown').each(function (index, element) {
		let el_pos = $(this).offset().top - $(window).scrollTop(),
			el_height = $(this).outerHeight(true);
		if (el_height + el_pos + 15 > windowHeight) {
			$(this).addClass('dropdown-scroll');
			$(this).css('height', windowHeight - el_pos - 15 + 'px');
		} else {
			$(this).removeClass('dropdown-scroll');
			$(this).css('height', 'auto');
		}
	});
}
$(document).ready(function () {
	// open modal
	btn = $('#menu-icon'),
		modal = $('.amegamenu_mobile-cover, .amegamenu_mobile-modal, #mobile_top_menu_wrapper');
	btn.off();
	btn.on('click', function(event) {
		$('html').addClass('amegamenu_mobile-open');
		modal.fadeIn();
	});

	// close modal
	$('.amegamenu_mobile-modal').click(function() {
		var select = $('#mobile_top_menu_wrapper');
		if ($(event.target).closest(select).length)
			return;
		modal.fadeOut(function () {
			$('html').removeClass('amegamenu_mobile-open');
			$('.mobile-amega-menu .open').removeClass('open');
			$('.mobile-amega-menu .menu-active').removeClass('menu-active');
			$('#mobile_top_menu_wrapper').attr('data-level', 0);
		});
	});
	$('.megamenu_mobile-btn-close').on('click', function(event) {
		modal.fadeOut(function () {
			$('html').removeClass('amegamenu_mobile-open');
			$('.mobile-amega-menu .open').removeClass('open');
			$('.mobile-amega-menu .menu-active').removeClass('menu-active');
			$('#mobile_top_menu_wrapper').attr('data-level', 0);
		});
	});
	

	var timerId_0=false;
	if($('#amegamenu').length >0){
		right_position();				
	
	 $(window).on('load resize scroll', function() {
		clearTimeout(timerId_0);
		timerId_0 = setTimeout(function () {
			right_position();
			dropdown_scroll();
		},10);
	});
	}
	 // mobile
	$(".arrow_down").on("click",function(){
	 	$(this).css("display","none");
	 	$(this).next().css("display","inline");
	 	$(this).closest(".amenu-item").find(".adropdown-mobile").slideDown();
	});
	$(".arrow_up").on("click",function(){
	 	$(this).css("display","none");
	 	$(this).prev().css("display","inline");
	 	$(this).closest(".amenu-item").find(".adropdown-mobile").slideUp();
	});

	$('.mobile_item_wrapper').on('click', function() {
		$('#mobile_top_menu_wrapper').attr('data-level', +$('#mobile_top_menu_wrapper').attr('data-level') + 1);
		$('.amegamenu_mobile-modal').animate({scrollTop: 0},100);
		$(this).closest('.mobile_item_wrapper').addClass('open').delay(50).queue(function(){
			$(this).next('.adropdown-mobile').addClass('menu-active').dequeue();
			
		});
	});
	$('.mobile_item_wrapper a').on('click', function(e) {
		e.preventDefault();
	});
	$('.megamenu_mobile-btn-back').on('click', function() {
		$('.menu-active').removeClass('menu-active').delay(300).queue(function(){
			$(this).prev('.mobile_item_wrapper.open').removeClass('open').dequeue();;
		});
		$('#mobile_top_menu_wrapper').attr('data-level', +$('#mobile_top_menu_wrapper').attr('data-level') - 1);
	});
});
