/**
 (C) Copryright https://www.ryviu.com
 
**/

(function ($) {
	// "use strict";
	$(document).ready(function ($) {

		$('.ryviu_settings_reviews').each(function () {
			let val = $(this).val();
			if (val === 10) {
				$(this).next().next('p.custom_position_display_reviews').show();
			}
		});

		$('.ryviu_settings_reviews').change(function () {
			let val = $(this).val();
			let pcode = $(this).parent().find('p.custom_position_display_reviews')[0];
			if (val === '10') {
				$(pcode).show();
			} else {
				$(pcode).hide();
			}
		});
	});

})(jQuery);