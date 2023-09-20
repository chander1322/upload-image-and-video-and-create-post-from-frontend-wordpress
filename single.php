<?php
/**
 * The template for displaying singular post-types: posts, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

while ( have_posts() ) :
	the_post();
	?>

<main id="content" <?php post_class( 'site-main' ); ?>>

	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
	<?php endif; ?>

	<div class="page-content">
		<?php the_content(); ?>
		<div class="post-tags">
			<?php the_tags( '<span class="tag-links">' . esc_html__( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
		</div>
		<?php wp_link_pages(); ?>
	</div>
	<!-- display the metadata on the single post page -->
	<?php $video_url = get_post_meta(get_the_ID(), 'video_url', true); 
		if (!empty($video_url)) {
			?>
			<h3>Post Meta</h3>
			<video width="320" height="240" controls>
			  <source src="<?php echo $video_url ; ?>" type="video/mp4">
			</video>
			<?php
		}
	?>
	<?php comments_template(); ?>

</main>

	<?php
endwhile;
