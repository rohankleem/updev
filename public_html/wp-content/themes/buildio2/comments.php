<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$twenty_twenty_one_comment_count = get_comments_number();
?>


<div class="container">

<div id="comments" class="comments-area default-max-width <?php echo get_option( 'show_avatars' ) ? 'show-avatars' : ''; ?>">

	<?php
	if ( have_comments() ) :
		?>
		<h2 class="comments-title">
			<?php if ( '1' === $twenty_twenty_one_comment_count ) : ?>
				<?php esc_html_e( '1 comment', 'twentytwentyone' ); ?>
			<?php else : ?>
				<?php
				printf(
					/* translators: %s: Comment count number. */
					esc_html( _nx( '%s comment', '%s comments', $twenty_twenty_one_comment_count, 'Comments title', 'twentytwentyone' ) ),
					esc_html( number_format_i18n( $twenty_twenty_one_comment_count ) )
				);
				?>
			<?php endif; ?>
		</h2><!-- .comments-title -->

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'avatar_size' => 60,
					'style'       => 'ol',
					'short_ping'  => true,
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_pagination(
			array(
				'before_page_number' => "Page" . ' ',
				'mid_size'           => 0,
				'prev_text'          => sprintf(
					'%s <span class="nav-prev-text">%s</span>',
					"x",
					esc_html__( 'Older comments', 'twentytwentyone' )
				),
				'next_text'          => sprintf(
					'<span class="nav-next-text">%s</span> %s',
					esc_html__( 'Newer comments', 'twentytwentyone' ),
					"y"
				),
			)
		);

		if ( ! comments_open() ) :
			echo '<p class="no-comments">' . 'Comments are closed.' . '</p>';
		endif;
	?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => esc_html__( 'Leave a comment', 'twentytwentyone' ),
			'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h2>',
			'class_form'         => 'comment-form', // Apply Bootstrap class to the comment form
			'comment_notes_before' => '', // Remove the comment notes
			'comment_notes_after' => '', // Remove the comment notes
			'submit_button' => '<button class="btn btn-primary" type="submit" id="submit">Submit</button>', // Style the submit button with Bootstrap class
			'fields' => apply_filters( 'comment_form_default_fields', array(
				'author' =>
				  '<div class="form-group">' .
				  '<label for="author">' . esc_html__( 'Name', 'domainreference' ) . '</label> ' .
				  ( $req ? '<span class="required">*</span>' : '' ) .
				  '<input class="form-control" id="author" name="author" required type="text" value="' . esc_attr( $commenter['comment_author'] ) .
				  '" size="30"' . $aria_req . ' /></div>',
				  'email' =>
				  '<div class="form-group">' .
				  '<label for="email">' . esc_html__( 'Email', 'domainreference' ) . '</label> ' .
				  ( $req ? '<span class="required">*</span>' : '' ) .
				  '<input class="form-control" id="email" name="email" type="text" required value="' . esc_attr(  $commenter['comment_author_email'] ) .
				  '" size="30"' . $aria_req . ' /></div>',
				  'url' =>
				  '<div class="form-group">' .
				  '<label for="url">' . esc_html__( 'Website', 'domainreference' ) . '</label>' .
				  '<input class="form-control" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
				  '" size="30" /></div>'
			) ),
			'comment_field' => '<div class="form-group"><label for="comment">' . esc_html_x( 'Comment', 'noun', 'twentytwentyone' ) . '</label><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true" required></textarea></div>',
		)
	);
	?>

</div><!-- #comments -->


</div>
