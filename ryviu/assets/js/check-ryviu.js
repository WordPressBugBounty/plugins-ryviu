/**
 (C) Copryright https://www.ryviu.com
**/

// Ajax to check connect Ryviu
jQuery(document).ready(function ($) {
	if ($('a.ryviu-check-connect').length) {
		$('a.ryviu-check-connect').on('click', function () {
			$.ajax({
				url: ajaxurl,
				method: "POST",
				data: {
					action: 'ryviu_check_connect'
				},
				dataType: "json",

				success: function (res) {
					if (res.status == 'success') {
						$('.r-cl-connect.notice-error').hide();
						$('.r-cl-connect.notice-success').show();
					}
					alert(res.mes);
				}
			});
		});
	}

	if ($('a.ryviu-update-frontend').length) {
		$('a.ryviu-update-frontend').on('click', function () {
			$.ajax({
				url: ajaxurl,
				method: "POST",
				data: {
					action: 'ryviu_update_frontend'
				},
				dataType: "json",

				success: function (res) {
					let data = res.data;
					
					if (data.status == 'success') {
						if (window.location.href.indexOf("ryviu-setting-admin") !== -1) {
							setTimeout(function () {
								location.reload();
							}, 2000);
						}
						
						$('.r-cl-update.notice-success').removeClass('rpl--hide');
						$('.r-cl-update.notice-success').addClass('rpl--show');
						$('.r-cl-update.notice-info').addClass('rpl--hide');
					}
					alert(data.mes);
				}
			});
		});
	}
});