<?php
/**
 * Productcategorie.
 *
 * Volgorde uit de must-haves: H1 bovenaan, korte SEO-tekst die de categorie
 * beschrijft, dan het productoverzicht, en daaronder een langere tekst met de
 * juiste koppenstructuur.
 *
 * @package React2u
 */
get_header();

$term      = get_queried_object();
$term_id   = $term instanceof WP_Term ? (int) $term->term_id : 0;
$kort      = $term instanceof WP_Term ? trim( wp_strip_all_tags( (string) $term->description ) ) : '';
$onder     = $term_id ? (string) get_term_meta( $term_id, '_react2u_onder_tekst', true ) : '';
$onder     = react2u_prepare_content( (string) apply_filters( 'the_content', $onder ) );
?>
<main tabindex="-1" id="main" class="site-main">
	<div class="archive-page">
		<header class="archive-header">
			<div class="shell archive-header-inner">
				<?php react2u_breadcrumbs(); ?>
				<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php esc_html_e( 'Producten', 'react2u' ); ?></p>
				<h1 class="archive-title"><?php echo esc_html( $term instanceof WP_Term ? $term->name : __( 'Producten', 'react2u' ) ); ?></h1>
				<?php if ( '' !== $kort ) : ?>
					<p class="archive-intro"><?php echo esc_html( $kort ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="shell archive-body">
			<?php if ( have_posts() ) : ?>
				<div class="product-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						react2u_product_card( (int) get_the_ID() );
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
					<p><?php esc_html_e( 'In deze categorie staan nog geen producten.', 'react2u' ); ?></p>
					<a class="button button-primary" href="<?php echo esc_url( react2u_cta_url( 'contact' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'contact' ) ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( wp_strip_all_tags( $onder['content'] ) ) ) : ?>
				<div class="product-cat-text entry-content">
					<?php echo $onder['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>

		<section class="section section-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
			<div class="shell"><?php react2u_cta( array( 'variant' => 'quote', 'tone' => 'dark', 'rating' => true ) ); ?></div>
		</section>
	</div>
</main>
<?php
get_footer();
