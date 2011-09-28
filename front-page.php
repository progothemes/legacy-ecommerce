<?php
/**
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header();
global $wp_query, $post;
$options = get_option( 'progo_options' );
?>
<div id="container" class="container_12">
<div id="pagetop" class="slides">
<?php
$original_query = $wp_query;
$slides = get_posts('post_type=progo_homeslide&post_status=publish&posts_per_page=-1&orderby=menu_order&order=ASC');
$count = count($slides);
$oneon = false;
foreach ( $slides as $s ) {
	$on = '';
	if ( $oneon == false ) {
		$oneon = true;
		$on = ' on';
	}
	
	$slidecustom = get_post_meta($s->ID,'_progo_slidecontent');
	$slidecontent = (array) $slidecustom[0];
	$bg = ' '. $slidecontent['textcolor'];
	$thmID = get_post_thumbnail_id( $s->ID );
	if ( $thmID ) {
		$thm = get_post( $thmID );
		$bg .= ' custombg " style="background-image: url('. $thm->guid .')';
	}
	switch($slidecontent['type']) {
		case 'Text':
			echo '<div class="textslide slide'. $on . $bg .'"><div class="page-title">'. wp_kses($s->post_title,array()) .'</div>';
			echo '<div class="content productcol">'. apply_filters('the_content',$slidecontent['text']) .'</div></div>';
			break;
		case 'Image':
			echo '<div class="imageslide slide'. $on . $bg .'">'. wp_kses($s->post_title,array()) .'</div>';
			break;
		case 'Product':
			echo '<div class="product slide'. $on . $bg .'">';
			$oldpost = $post;
			//echo '<pre style="display:none">'. print_r($slidecontent,true) .'</pre>';
			$post = get_post($slidecontent['product']);
			//echo '<pre style="display:none">'. print_r($post,true) .'</pre>';
			wpsc_the_product();
			$post = get_post($slidecontent['product']);
			echo '<a href="'. wpsc_the_product_permalink() .'" class="product_image"><img alt="'. wpsc_the_product_title() .'" src="'. progo_product_image(290,290) .'" width="290" height="290" /></a>';
			echo '<div class="productcol grid_7"><div class="prodtitle">'. wpsc_the_product_title() .'</div>';
			progo_summary( 'View Details', 260 );
			echo '<div class="price">'. wpsc_the_product_price() .'</div>';
			if(wpsc_product_external_link(wpsc_the_product_id()) != '') {
				$action =  wpsc_product_external_link(wpsc_the_product_id());
			} else {
				$action = htmlentities(wpsc_this_page_url(), ENT_QUOTES, 'UTF-8' );
			}
			?>
									<form class="product_form"  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>f" id="product_<?php echo wpsc_the_product_id(); ?>f" >
                        <?php if (wpsc_have_variation_groups()) {
							echo '<a href="'. wpsc_the_product_permalink() .'" class="morebutton">Buy</a>'; 
						} else { ?>
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
					
											<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') { ?>
											<input class="wpsc_buy_button" type="submit" value="<?php echo wpsc_product_external_link_text( wpsc_the_product_id(), __( 'Buy Now', 'wpsc' ) ); ?>" onclick="return gotoexternallink('<?php echo $action; ?>', '<?php echo wpsc_product_external_link_target( wpsc_the_product_id() ); ?>')">
											<?php } else { ?>
										<input type="submit" value="<?php _e('Buy Now', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>f_submit_button"/>
											<?php }
											} ?>
						</form><!--close product_form-->
            <?php
			echo '</div></div>';
			$post = $oldpost;
			break;
	}
}
if ( $oneon == true && $count > 1 ) { ?>
<div class="ar"><a href="#p" title="Previous Slide"></a><a href="#n" class="n" title="Next Slide"></a></div>
<script type="text/javascript">
progo_timing = <?php $hsecs = absint($options['homeseconds']); echo $hsecs > 0 ? $hsecs * 1000 : "0"; ?>;
</script>
<?php
}
do_action('progo_pagetop'); ?>
</div>
<div id="main" class="grid_8">
<?php
rewind_posts();
$onfront = get_option( 'show_on_front' );
if ( isset( $options['frontpage'] ) ) {
	$onfront = $options['frontpage'];
}
switch ( $onfront ) {
	case 'featured':
		$sticky_array = get_option( 'sticky_products' );
		if ( !empty( $sticky_array ) ) {
			$old_query = $wp_query;
			$wp_query = new WP_Query( array(
						'post__in' => $sticky_array,
						'post_type' => 'wpsc-product',
						'numberposts' => -1,
						'order' => 'ASC'
					) );
					
		
				$GLOBALS['nzshpcrt_activateshpcrt'] = true;
				$image_width = get_option( 'product_image_width' );
				$image_height = get_option( 'product_image_height' );
				$featured_product_theme_path = wpsc_get_template_file_path( 'wpsc-products_page.php' );
		ob_start();
			include_once($featured_product_theme_path);
			$is_single = false;
			$output .= ob_get_contents();
			ob_end_clean();
			
				//Begin outputting featured product.  We can worry about templating later, or folks can just CSS it up.
				echo $output;
				//End output
				
				$wp_query = $old_query;
		}
		break;
	case 'posts':
		get_template_part( 'loop', 'index' );
		break;
	case 'page':
		if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry">
		<?php the_content(); ?>
		</div><!-- .entry -->
		</div><!-- #post-## -->
		<?php
		endwhile;
		break;
}
?>
</div><!-- #main -->
<?php 
if($options['frontpage'] == 'posts') {
	get_sidebar('blog');
} else {
	get_sidebar();
} ?>
</div><!-- #container -->
<?php get_footer(); ?>