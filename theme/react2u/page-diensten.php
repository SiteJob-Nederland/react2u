<?php
/**
 * Template Name: Dienstenoverzicht
 * Template Post Type: page
 *
 * Overzicht van de zes diensten van React2u. De kaarten komen rechtstreeks uit
 * inc/proof.php, zodat titel, URL en dienstkleur op de homepage, dit overzicht
 * en de detailpagina's gelijk blijven. De redactionele pagina-inhoud blijft via
 * de normale the_content-filters beschikbaar onder het overzicht.
 *
 * @package React2u
 */

get_header();

$services = (array) react2u_get( 'services', array() );
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', get_the_content() ) );
		$intro    = has_excerpt()
			? get_the_excerpt()
			: __( 'Bekijk de diensten van React2u voor verzuim, re-integratie, preventie en gezond werken.', 'react2u' );
		$aanbod_url   = $services ? '#diensten-aanbod' : react2u_cta_url( 'contact' );
		$aanbod_label = $services ? __( 'Bekijk alle diensten', 'react2u' ) : react2u_cta_label( 'contact' );
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'diensten-overzicht' ); ?> data-variant="diensten">
			<header class="diensten-hero tone-dark" aria-labelledby="diensten-title">
				<div class="shell diensten-hero-inner">
					<div class="diensten-hero-copy">
						<?php react2u_breadcrumbs(); ?>
						<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Onze dienstverlening', 'react2u' ); ?></p>
						<h1 class="diensten-hero-title" id="diensten-title"><?php the_title(); ?></h1>
						<?php if ( '' !== trim( $intro ) ) : ?>
							<p class="diensten-hero-intro"><?php echo esc_html( $intro ); ?></p>
						<?php endif; ?>
						<div class="diensten-hero-actions">
							<a class="button button-primary" href="<?php echo esc_url( $aanbod_url ); ?>"><?php echo esc_html( $aanbod_label ); ?></a>
							<a class="button button-outline-light" href="<?php echo esc_url( react2u_cta_url( 'quote' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'quote' ) ); ?></a>
						</div>
					</div>

					<div class="diensten-spectrum" aria-hidden="true">
						<?php foreach ( $services as $service ) : ?>
							<?php $service_slug = sanitize_html_class( (string) ( $service['slug'] ?? '' ) ); ?>
							<span class="diensten-spectrum-dot service-card--<?php echo esc_attr( $service_slug ); ?>"></span>
						<?php endforeach; ?>
					</div>
				</div>
			</header>

			<?php if ( $services ) : ?>
				<section class="diensten-aanbod" id="diensten-aanbod" aria-labelledby="diensten-aanbod-title">
					<div class="shell">
						<header class="diensten-section-heading">
							<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Zes routes', 'react2u' ); ?></p>
							<h2 id="diensten-aanbod-title"><?php esc_html_e( 'Waar kunnen we je bij helpen?', 'react2u' ); ?></h2>
						</header>

						<ul class="diensten-kaarten" role="list">
							<?php foreach ( $services as $index => $service ) : ?>
								<?php
								$service_slug  = sanitize_html_class( (string) ( $service['slug'] ?? '' ) );
								$service_title = (string) ( $service['title'] ?? '' );
								$service_path  = (string) ( $service['path'] ?? '' );
								?>
								<li class="diensten-kaart service-card service-card--<?php echo esc_attr( $service_slug ); ?>">
									<a class="diensten-kaart-link" href="<?php echo esc_url( home_url( $service_path ) ); ?>">
										<span class="diensten-kaart-nummer" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
										<span class="service-dot" aria-hidden="true"></span>
										<h3 class="diensten-kaart-title service-title"><?php echo react2u_text( $service_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
										<p class="diensten-kaart-text service-text"><?php echo react2u_text( $service['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
										<span class="diensten-kaart-action">
											<?php esc_html_e( 'Bekijk deze dienst', 'react2u' ); ?>
											<?php echo react2u_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $prepared['content'] ) ) ) : ?>
				<section class="diensten-verhaal" aria-label="<?php esc_attr_e( 'Over onze dienstverlening', 'react2u' ); ?>">
					<div class="shell diensten-verhaal-inner">
						<?php react2u_toc( $prepared['toc'] ); ?>
						<div class="entry-content diensten-entry-content"><?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- al door the_content-filters. ?></div>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( '' !== $prepared['faq'] ) : ?>
				<div class="shell resource-faq diensten-faq">
					<?php react2u_faq( $prepared['faq'] ); ?>
				</div>
			<?php endif; ?>

			<section class="diensten-final-cta resource-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
				<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'layout' => 'section', 'tone' => 'dark', 'rating' => true ) ); ?></div>
			</section>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
