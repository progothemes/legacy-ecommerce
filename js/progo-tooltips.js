function progo_newtip( x, y, message, url ) {
	return '<a href="'+ progo_adminurl + url +'" class="ptip" style="left: '+ x +'px; top: '+ y +'px" target="_blank"><span>'+ message +'</span></a>';
}

function progo_tooltip_init() {
	var ptips = '';
	// tooltips for every page / header area
	ptips += progo_newtip( -41, 20, 'Customize your Background via Appearance > Background', 'themes.php?page=custom-background' );
	ptips += progo_newtip( -11, 82, 'Customize your Menu via Appearance > Menu', 'nav-menus.php' );
	ptips += progo_newtip( 15, 12, 'Customize your Logo via Appearance > Theme Options', 'themes.php?page=progo_admin#progo_theme' );
	ptips += progo_newtip( 833, 17, 'Customize your Customer Support area via Appearance > Theme Options', 'themes.php?page=progo_admin#progo_info' );
	
	if ( jQuery('body').hasClass('home') ) {
		ptips += progo_newtip( -11, 129, 'Edit your Homepage Slides via Appearance > Homepage Slides', 'edit.php?post_type=progo_homeslide' );
	}
	// for SLOGAN, we want to know how long the LOGO is
	var pos = jQuery('#slogan').position();
	ptips += progo_newtip( pos.left, 28, 'Customize your Slogan via Appearance > Theme Options', 'themes.php?page=progo_admin#progo_info' );
	
	jQuery('#page').append(ptips);
	
	// sidebar-specific tips
	ptips = '';
	ptips += progo_newtip( -11, 0, 'Control which Widgets appear in your sidebars, via Appearance > Widgets', 'widgets.php' );
	if ( jQuery('body').hasClass('page') && jQuery('#wp-admin-bar-edit').size() > 0 ) {
		var url = jQuery('#wp-admin-bar-edit a:first').attr('href');
		ptips += progo_newtip( 41, 0, 'Choose which Sidebar is displayed on this Page, via the "Sidebar" metabox', url.substr(url.indexOf('post.php')) );
	}
	jQuery('#secondary').append(ptips);
	
	// footer-specific tips
	ptips = '';
	ptips += progo_newtip( -20, -20, 'Customize which links appear here, via the "Footer Links" menu in Appearance > Menus', 'nav-menus.php' );
	ptips += progo_newtip( -20, 6, 'Control this bottom line of text via Appearance "Copyright Notice" Theme Option', 'themes.php?page=progo_admin#progo_info' );
	jQuery('#ftr .grid_8').append(ptips);
}

jQuery(function() {
	progo_tooltip_init();
});