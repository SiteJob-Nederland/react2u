<?php
/**
 * Auteurskaart: foto, functie, bio, LinkedIn, recente artikelen.
 *
 * layout 'byline' is de compacte regel direct onder de titel, 'full' de kaart
 * onderaan het artikel.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$author_id = (int) ( $args['author_id'] ?? 0 );
if ( ! $author_id ) {
	return;
}

$layout   = (string) ( $args['layout'] ?? 'full' );
$recent   = (bool) ( $args['recent'] ?? false );
$name     = (string) get_the_author_meta( 'display_name', $author_id );
$role     = react2u_author_job_title( $author_id );
$bio      = (string) get_the_author_meta( 'description', $author_id );
$socials  = react2u_author_socials( $author_id );
$archive  = (string) get_author_posts_url( $author_id );

if ( 'byline' === $layout ) :
	?>
	<div class="byline">
		<a class="byline-author" href="<?php echo esc_url( $archive ); ?>" rel="author">
			<span class="byline-photo"><?php echo wp_kses_post( react2u_author_photo( $author_id, 48 ) ); ?></span>
			<span class="byline-name">
				<small><?php esc_html_e( 'Geschreven door', 'react2u' ); ?></small>
				<strong><?php echo esc_html( $name ); ?><?php echo '' !== $role ? ', ' . esc_html( $role ) : ''; ?></strong>
			</span>
		</a>

		<span class="byline-detail">
			<small><?php esc_html_e( 'Gepubliceerd', 'react2u' ); ?></small>
			<time datetime="<?php echo esc_attr( (string) get_the_date( DATE_ATOM ) ); ?>"><?php echo esc_html( (string) get_the_date() ); ?></time>
		</span>

		<?php if ( get_the_modified_date( 'Ymd' ) !== get_the_date( 'Ymd' ) ) : ?>
			<span class="byline-detail">
				<small><?php esc_html_e( 'Bijgewerkt', 'react2u' ); ?></small>
				<time datetime="<?php echo esc_attr( (string) get_the_modified_date( DATE_ATOM ) ); ?>"><?php echo esc_html( (string) get_the_modified_date() ); ?></time>
			</span>
		<?php endif; ?>

		<span class="byline-detail">
			<small><?php esc_html_e( 'Leestijd', 'react2u' ); ?></small>
			<?php
			$minutes = react2u_reading_time();
			echo esc_html( sprintf( /* translators: %d: aantal minuten */ _n( '%d minuut', '%d minuten', $minutes, 'react2u' ), $minutes ) );
			?>
		</span>
	</div>
	<?php
	return;
endif;
?>
<aside class="author-card" aria-labelledby="author-card-name">
	<div class="author-card-photo"><?php echo wp_kses_post( react2u_author_photo( $author_id, 112 ) ); ?></div>

	<div class="author-card-body">
		<p class="mini-label"><?php esc_html_e( 'Over de auteur', 'react2u' ); ?></p>
		<h2 class="author-card-name" id="author-card-name"><?php echo esc_html( $name ); ?></h2>

		<?php if ( '' !== $role ) : ?>
			<p class="author-card-role"><?php echo esc_html( $role ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $bio ) : ?>
			<p class="author-card-bio"><?php echo esc_html( $bio ); ?></p>
		<?php endif; ?>

		<div class="author-card-links">
			<a class="arrow-link" href="<?php echo esc_url( $archive ); ?>">
				<?php esc_html_e( 'Alle artikelen', 'react2u' ); ?> <?php echo react2u_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<?php if ( $socials ) : ?>
			<ul class="author-card-socials">
				<?php foreach ( $socials as $social ) : ?>
					<li>
						<a href="<?php echo esc_url( $social['url'] ); ?>" rel="me noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
							<?php echo react2u_icon( $social['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $social['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php
		if ( $recent ) :
			$posts = get_posts(
				array(
					'author'              => $author_id,
					'post_type'           => REACT2U_ARTICLE_TYPES,
					'posts_per_page'      => 3,
					'post__not_in'        => array( (int) get_the_ID() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);

			if ( $posts ) :
				?>
				<div class="author-card-recent">
					<p class="mini-label"><?php esc_html_e( 'Recent van deze auteur', 'react2u' ); ?></p>
					<ul>
						<?php foreach ( $posts as $item ) : ?>
							<li>
								<a href="<?php echo esc_url( (string) get_permalink( $item ) ); ?>"><?php echo esc_html( (string) get_the_title( $item ) ); ?></a>
								<span><?php echo esc_html( react2u_content_label( (string) get_post_type( $item ) ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php
			endif;
		endif;
		?>
	</div>
</aside>
