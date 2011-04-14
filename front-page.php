<?php
/**
 * Template Name: Featured Products
 *
 * Featured Products page template
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header();
global $wp_query, $post;
?>
<div id="container" class="container_12">
<div id="pagetop" class="slides">
<?php
$slides = (array) get_option( 'progo_slides' );
unset($slides['count']);
$count = count($slides);
$oneon = false;
for ( $i = 0; $i < $count; $i++ ) {
	$show = $slides[$i]['show'];
	$on = '';
	if($oneon == false && $show != 'new') {
		$oneon = true;
		$on = ' on';
	}
	
	switch($show) {
		case 'text':
			echo "<div class='slide$on page-title'>". wp_kses($slides[$i]['text'],array()) ."</div>";
			break;
		case 'product':
			$oldpost = $post;
			echo "<div class='slide$on product'>";
			$post = get_post($slides[$i]['product']);
			echo '<a href="'. wpsc_the_product_permalink() .'" class="product_image"><img alt="'. wpsc_the_product_title() .'" src="'. wpsc_the_product_image() .'" width="290" height="290" /></a>';
			echo '<div class="productcol grid_7"><div class="prodtitle">'. wpsc_the_product_title() .'</div>';
			progo_summary( 'View Details', 260 );
			echo '<div class="price">'. wpsc_the_product_price() .'</div>';
			echo "</div></div>";
			$post = $oldpost;
			break;
	}
}
if ( $oneon == true && $count > 1 ) {
	echo '<div class="ar"><a href="#p" title="Previous Slide"></a><a href="#n" class="n" title="Next Slide"></a></div>';
}
// i forget why this is here...
do_action('progo_pagetop'); ?>
</div>
<div id="main" role="main" class="grid_8">
<?php
$options = get_option( 'progo_options' );
switch ( $options['frontpage'] ) {
	case 'featured':
		echo '<!-- progohomecheck featured -->';
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
		echo '<!-- progohomecheck posts -->';
		get_template_part( 'loop', 'index' );
		break;
	case 'page':
		echo '<!-- progohomecheck page -->';
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