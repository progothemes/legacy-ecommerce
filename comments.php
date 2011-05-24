<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to progo_comment which is
 * located in the functions.php file.
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */
?>

			<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'progo' ); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;

$req = get_option( 'require_name_email' );
$aria_req = ( $req ? " aria-required='true'" : '' );
$args = array(
	'fields' => array(
		'author' => '<tr><td width="300"><p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
		            '<br /><input id="author" name="author" class="text" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="42"' . $aria_req . ' /></p></td>',
		'email'  => '<td width="296"><p class="comment-form-email"><label for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
		            '<br /><input id="email" name="email" class="text" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="42"' . $aria_req . ' /></p></td></tr>'
	),
	'comment_field'        => '<tr><td colspan="2"><p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" cols="89" rows="5" aria-required="true"></textarea></p></td></tr>',
	'comment_notes_before' => '<table width="596" cellpadding="0" cellspacing="0">',
	'comment_notes_after' => '</table>',
	'id_submit'            => 'commentsubmit',
	'label_submit'         => __( 'submit comment' )
);
comment_form( $args );

if ( have_comments() ) : ?>
			<h3 id="comments-title"><?php
			printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), 'progo' ),
			number_format_i18n( get_comments_number() ), '&ldquo;' . get_the_title() . '&rdquo;' );
			?></h3>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'progo' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'progo' ) ); ?></div>
			</div> <!-- .navigation -->
<?php endif; // check for comment navigation ?>

			<ol class="commentlist">
				<?php
				$args = array(
					'callback' => 'progo_comments'
				);
				wp_list_comments($args); ?>
			</ol>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'progo' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'progo' ) ); ?></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'progo' ); ?></p>
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

</div><!-- #comments -->
