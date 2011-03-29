<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header();
$options = get_option('progo_options');
?>
        <div id="container" class="container_12">
<?php if ( have_posts() ) while ( have_posts() ) : the_post();
$showedit = true;
?>
			<div id="main" role="main" class="grid_8">
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<h1 class="page-title"><?php the_title(); ?></h1>
<div class="grid_8 entry">
<?php the_content(); ?>
</div><!-- .entry -->
</div><!-- #post-## -->
</div><!-- #main -->
<?php endwhile; ?>
</div><!-- #container -->
<!-- #THISISTHEDEFAULTPAGE -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
