<?php
/**
 * The Template for displaying all single posts.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Direct 1.0
 */

get_header(); ?>
        <div id="container" class="container_12">
			<div id="main" role="main" class="grid_8">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry">
						<?php the_content(); ?>
					</div><!-- .entry -->

					<div class="entry-utility">
						<?php edit_post_link( __( 'Edit', 'progo' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->
<?php endwhile; // end of the loop. ?>

			</div><!-- #main -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
