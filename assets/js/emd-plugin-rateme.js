jQuery(document).ready(function($){
	var container = $('.emd-show-rateme');
	if (container.length) {
		container.find('a').click(function() {
			container.remove();

			var rateAction = $(this).data('rate-action');
			var ratePlugin = $(this).data('plugin');
			$.ajax({
				url       : ajaxurl,
				method    : 'POST',
				data      : {
					'action'     : ratePlugin+'_show_rateme',
					'rate_action': rateAction,
					'rateme_nonce': container.find('ul:first').attr('data-nonce'),
				},
			});

			if ('do-rate' !== rateAction && 'upgrade-now' !== rateAction) {
				return false;
			}
		});
	}
});

