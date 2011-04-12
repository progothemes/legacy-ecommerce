function progo_anotherslide() {
	var num = jQuery('#numslides');
	var n = parseInt(num.val(),10) + 1;
	var data = {
		action: 'progo_homeslide_ajax',
		slideaction: 'new',
		slidenum: n
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		jQuery('#normal-sortables').append(response);
	});
	num.val(n);
	
	return false;
}

function progo_slidefor( sel ) {
	var whi = '.'+ sel.val();
	if(whi=='.') {
	sel.parent().siblings().hide();
	} else {
		sel.parent().siblings().hide();
		sel.parent().siblings(whi).show();
	}
}

function progo_slideremove( lnk ) {
	lnk.parents('.postbox').remove();
	var num = jQuery('#numslides');
	var n = parseInt(num.val(),10) - 1;
	num.val(n);
	return false;
}

jQuery(function() {
	var td = jQuery('#poststuff').parent().css('padding',0);
	td.prev().remove();
	td.parents('table').siblings('h3').remove();
});