<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */

get_header(); ?>
    <div id="container" class="container_12">
        <div id="main" role="main" class="grid_8">

<?php if ( have_posts() ) : ?>
				<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'progo' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
				<?php
				/* Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called loop-search.php and that will be used instead.
				 */
				 get_template_part( 'loop', 'search' );
				?>
<?php else : ?>
				<div id="post-0" class="post no-results not-found">
					<h2 class="entry-title"><?php _e( 'Nothing Found', 'twentyten' ); ?></h2>
					<div class="entry">
						<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'progo' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- .entry -->
				</div><!-- #post-0 -->
<?php endif; ?>
			</div><!-- #main -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
