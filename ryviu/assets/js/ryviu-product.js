//Ryviu.com

jQuery(document).ready(function ($) {
	$('.column-ryviu_meta > a').on('click', function () {
		let thiss = $(this);
		thiss.text('Updating...');
		$.ajax({
			url: ajaxurl,
			method: "POST",
			data: {
				product_id: $(this).data('pid'),
				action: 'ryviu_update_meta'
			},
			dataType: "json",

			success: function (res) {
				thiss.css({ "display": "block", "color": "green", "font-weight": "bold" });
				thiss.text('Updated');
				if (res.meta_info) {
					let meta_info = res.meta_info;
					meta_info = meta_info.replace('"', '');
					let meta_split = meta_info.split(";");
					let total = parseInt(meta_split[0]);
					let avg = round(parseFloat(meta_split[1]), 2);
					if (total > 1) {
						thiss.parent().find('.rv-number').html(total + ' reviews');
					} else {
						thiss.parent().find('.rv-number').html(total + ' review');
					}
					thiss.parent().find('.rv-rating').html('★ ' + avg);
				}
			}
		});
	});
});
