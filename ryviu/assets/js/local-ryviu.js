/**
 (C) Copryright https://www.ryviu.com
**/

(function ($) {
	// "use strict";
	$(document).ready(function () {
		//Nothing to do
		let element_trigger_click = '.ryviu_reviews_tab_tab > a';

		if (ryviu_app.element_trigger_click && ryviu_app.element_trigger_click != '') {
			element_trigger_click = ryviu_app.element_trigger_click;
		}

		if (ryviu_app.active_reviews_tab == 1) {
			$(element_trigger_click).trigger('click');
		}

		if (ryviu_app.position_display == 1 || element_trigger_click != '.ryviu_reviews_tab_tab > a') {
			$(document).on('click', '.product-widget__ryviu', function () {
				if ($('ryviu-widget').length) {
					$(element_trigger_click).trigger('click');
					$('html, body').animate({
						scrollTop: $("ryviu-widget").offset().top
					}, 0)
				}
			});
		}
	});

})(jQuery);