<?php
/**
 * Kaart voor een artikel in een overzicht of bij "gerelateerd".
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$priority = (bool) ( $args['priority'] ?? false );
$post_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$post_type = (string) get_post_type( $post_id );
$featured  = (bool) ( $args['featured'] ?? false );
$author_id = (int) get_post_field( 'post_author', $post_id );
?>
<article class="card<?php echo $featured ? ' is-featured' : ''; ?> type-<?php echo esc_attr( $post_type ); ?>">
	<?php /* Eén klikvlak over de hele kaart, met de titel als toegankelijke naam. */ ?>
	<a class="card-link" href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>">
		<span class="sr-only"><?php echo esc_html( (string) get_the_title( $post_id ) ); ?></span>
	</a>

	<div class="card-media">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<?php
			echo wp_get_attachment_image(
				get_post_thumbnail_id( $post_id ),
				'react2u-card',
				false,
				array(
					'class'    => 'card-image',
					'loading'  => $priority ? 'eager' : 'lazy',
					'fetchpriority' => $priority ? 'high' : 'low',
					'decoding' => 'async',
					'sizes'    => '(max-width: 700px) 100vw, 380px',
				)
			);
			?>
		<?php else : ?>
			<span class="card-image is-fallback" aria-hidden="true"><?php echo react2u_logo_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
	</div>

	<div class="card-body">
		<p class="card-type"><?php echo esc_html( react2u_content_label( $post_type ) ); ?></p>
		<h3 class="card-title"><?php echo esc_html( (string) get_the_title( $post_id ) ); ?></h3>
		<p class="card-text"><?php echo esc_html( react2u_summary( $post_id, 120 ) ); ?></p>

		<p class="card-meta">
			<?php if ( $author_id ) : ?>
				<span class="card-author"><?php echo esc_html( (string) get_the_author_meta( 'display_name', $author_id ) ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( (string) get_the_date( DATE_ATOM, $post_id ) ); ?>"><?php echo esc_html( (string) get_the_date( '', $post_id ) ); ?></time>
			<span class="card-reading">
				<?php
				$minutes = react2u_reading_time( $post_id );
				echo esc_html( sprintf( /* translators: %d: aantal minuten */ _n( '%d min', '%d min', $minutes, 'react2u' ), $minutes ) );
				?>
			</span>
		</p>
	</div>
</article>
