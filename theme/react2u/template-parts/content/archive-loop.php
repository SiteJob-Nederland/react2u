<?php
/**
 * Gedeeld overzichtssjabloon: blog, kennisbank, categorie, auteur en zoeken.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$eyebrow = (string) ( $args['eyebrow'] ?? '' );
$title   = (string) ( $args['title'] ?? '' );
$intro   = (string) ( $args['intro'] ?? '' );
$before  = (string) ( $args['before'] ?? '' );
?>
<div class="archive-page">
	<header class="archive-header">
		<div class="shell archive-header-inner">
			<?php react2u_breadcrumbs(); ?>

			<?php if ( '' !== $eyebrow ) : ?>
				<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h1 class="archive-title"><?php echo esc_html( $title ); ?></h1>

			<?php if ( '' !== $intro ) : ?>
				<p class="archive-intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>

			<?php echo $before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- eigen markup uit het aanroepende sjabloon. ?>
		</div>
	</header>

	<div class="shell archive-body">
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/card', null, array( 'priority' => 0 === (int) $GLOBALS['wp_query']->current_post, 'post_id' => get_the_ID() ) );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'           => 1,
					'prev_text'          => __( 'Vorige', 'react2u' ),
					'next_text'          => __( 'Volgende', 'react2u' ),
					'screen_reader_text' => __( 'Paginanavigatie', 'react2u' ),
				)
			);
			?>
		<?php else : ?>
			<div class="empty-state">
				<p><?php esc_html_e( 'Er zijn hier nog geen artikelen. Lees intussen verder bij de informatie voor werkgevers en werknemers.', 'react2u' ); ?></p>
				<?php if ( is_home() || is_post_type_archive( 'react2u_kennisbank' ) ) : ?>
					<?php $employer_page = get_page_by_path( 'werkgevers' ); ?>
					<p><a href="<?php echo esc_url( $employer_page instanceof WP_Post && 'publish' === $employer_page->post_status ? get_permalink( $employer_page ) : home_url( '/diensten/' ) ); ?>"><?php esc_html_e( 'Voor werkgevers', 'react2u' ); ?></a> · <a href="<?php echo esc_url( home_url( '/werknemers/' ) ); ?>"><?php esc_html_e( 'Voor werknemers', 'react2u' ); ?></a></p>
				<?php endif; ?>
				<a class="button button-primary" href="<?php echo esc_url( react2u_cta_url( 'contact' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'contact' ) ); ?></a>
			</div>
		<?php endif; ?>
	</div>

	<section class="section section-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
		<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark', 'rating' => true ) ); ?></div>
	</section>
</div>
