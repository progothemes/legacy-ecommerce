function progo_newtip( top, left, message ) {
	return '<div class="ptip" style="top: '+ top +'px; left: '+ left +'px"><span>'+ message +'</span></div>';
}

function progo_tooltip_init() {
	var ptips = '';
	ptips += progo_newtip( 20, -41, 'Customize your Background via <a href="'+ progo_adminurl+'themes.php?page=custom-background">Appearance > Background</a>' );
	ptips += progo_newtip( 82, -11, 'Customize your Menu via <a href="'+ progo_adminurl+'nav-menus.php">Appearance > Menu</a>' );
	ptips += progo_newtip( 12, 15, 'Customize your Logo via <a href="'+ progo_adminurl+'themes.php?page=progo_admin">Appearance > Theme Options</a>' );
	ptips += progo_newtip( 129, -11, 'Edit your Homepage Slides via <a href="'+ progo_adminurl+'themes.php?page=progo_home_slides">Appearance > Homepage Slides</a>' );
	
	jQuery('#page').append(ptips);
}

jQuery(function() {
	progo_tooltip_init();
});