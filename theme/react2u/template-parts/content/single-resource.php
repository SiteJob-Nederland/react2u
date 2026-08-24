<?php
/**
 * Gedeeld artikelsjabloon voor blog en kennisbank.
 *
 * Eén sjabloon, twee varianten: het posttype bepaalt het label en de
 * accentnuance, de opbouw is identiek. Dat is bewust — de eisen aan een
 * kennisbankstuk en een blogartikel zijn dezelfde, en één sjabloon betekent één
 * plek om ze te onderhouden.
 *
 * Volgorde: kruimelpad, H1, auteursblok, banner, inhoudsopgave, tekst met
 * inline CTA, meelopende CTA, FAQ, harde CTA, auteurskaart, gerelateerd.
 *
 * @package React2u
 */

$post_id   = (int) get_the_ID();
$post_type = (string) get_post_type( $post_id );
$author_id = (int) get_post_field( 'post_author', $post_id );
$is_kb     = 'react2u_kennisbank' === $post_type;
$ctas      = react2u_content_ctas( $post_id );

$prepared = react2u_prepare_content( (string) apply_filters( 'the_content', get_the_content() ) );
$body     = $prepared['content'];

/*
 * Inline CTA's: één vóór het derde hoofdstuk, één vóór het zesde. Een externe
 * redactietool levert hooguit één link naar de contactpagina mee; zonder deze
 * blokken staat er middenin een lang artikel niets om op te klikken.
 */
$body = react2u_weave_ctas( $body, $is_kb ? 'quote' : 'pricing', 'callback' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'resource' ); ?> data-variant="<?php echo esc_attr( $is_kb ? 'kennisbank' : 'blog' ); ?>">

	<header class="resource-header">
		<div class="shell resource-header-inner">
			<?php react2u_breadcrumbs(); ?>

			<p class="eyebrow">
				<span class="eyebrow-dot" aria-hidden="true"></span>
				<?php echo esc_html( react2u_content_label( $post_type ) ); ?>
				<?php
				$term = react2u_primary_term( $post_id, $post_type );
				if ( $term ) :
					?>
					<span class="eyebrow-term"><?php echo esc_html( $term->name ); ?></span>
				<?php endif; ?>
			</p>

			<h1 class="resource-title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="resource-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<?php react2u_author_card( $author_id, array( 'layout' => 'byline' ) ); ?>
		</div>
	</header>

	<div class="shell resource-banner">
		<?php react2u_banner( array( 'post_id' => $post_id, 'label' => react2u_content_label( $post_type ) ) ); ?>
	</div>

	<div class="shell resource-body">
		<div class="resource-main">
			<?php react2u_toc( $prepared['toc'] ); ?>

			<?php /* Al door de the_content-filters heen; kses zou embeds slopen. */ ?>
			<div class="entry-content"><?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

			<?php
			$sources = get_post_meta( $post_id, '_react2u_sources', true );
			if ( is_array( $sources ) && $sources ) :
				?>
				<section class="resource-sources" aria-labelledby="sources-title">
					<h2 id="sources-title"><?php esc_html_e( 'Bronnen', 'react2u' ); ?></h2>
					<ol>
						<?php foreach ( $sources as $source ) : ?>
							<li><a href="<?php echo esc_url( (string) ( $source['url'] ?? '' ) ); ?>" rel="noopener noreferrer"><?php echo esc_html( (string) ( $source['label'] ?? '' ) ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Op desktop een kaart die binnen zijn eigen kolom meeloopt; op smallere
		 * schermen valt hij terug op de balk onderaan (.sticky-cta). Twee keer
		 * dezelfde vraag tegelijk tonen we bewust niet.
		 */
		?>
		<aside class="resource-aside" aria-label="<?php esc_attr_e( 'Direct contact', 'react2u' ); ?>">
			<div class="aside-inner">
				<?php
				react2u_cta(
					array(
						'variant' => 'quote',
						'layout'  => 'aside',
						'tone'    => 'light',
						'heading' => 'p',
						'rating'  => true,
						'person'  => true,
						'eyebrow' => __( 'Hulp nodig?', 'react2u' ),
						'text'    => __( 'Vertel wat je nodig hebt. We reageren binnen één werkdag.', 'react2u' ),
					)
				);
				?>
			</div>
		</aside>
	</div>

	<?php if ( '' !== $prepared['faq'] ) : ?>
		<div class="shell resource-faq">
			<?php react2u_faq( $prepared['faq'] ); ?>
		</div>
	<?php endif; ?>

	<section class="resource-final-cta" aria-label="<?php esc_attr_e( 'Volgende stap', 'react2u' ); ?>">
		<div class="shell">
			<?php react2u_cta( array( 'variant' => 'quote', 'layout' => 'section', 'tone' => 'dark', 'rating' => true ) ); ?>
		</div>
	</section>

	<?php if ( $author_id ) : ?>
		<div class="shell resource-author">
			<?php react2u_author_card( $author_id, array( 'layout' => 'full', 'recent' => true ) ); ?>
		</div>
	<?php endif; ?>

	<?php
	$related = new WP_Query(
		array(
			'post_type'           => $post_type,
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'date',
		)
	);

	if ( $related->have_posts() ) :
		?>
		<section class="section section-related" aria-labelledby="related-title">
			<div class="shell">
				<?php
				react2u_section_heading(
					array(
						'eyebrow' => react2u_content_label( $post_type ),
						'title'   => $is_kb
							? __( 'Verder lezen in de kennisbank', 'react2u' )
							: __( 'Meer uit de blog', 'react2u' ),
						'id'      => 'related-title',
					)
				);
				?>
				<div class="card-grid">
					<?php
					while ( $related->have_posts() ) :
						$related->the_post();
						get_template_part( 'template-parts/content/card', null, array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
	?>
</article>

<?php if ( $ctas ) : ?>
	<div class="sticky-cta" data-sticky-cta hidden>
		<div class="shell sticky-cta-inner">
			<p class="sticky-cta-text">
				<strong><?php esc_html_e( 'Wat betekent dit voor jou?', 'react2u' ); ?></strong>
				<span><?php esc_html_e( 'Antwoord binnen één werkdag.', 'react2u' ); ?></span>
			</p>
			<a class="button button-primary button-small" href="<?php echo esc_url( $ctas[0]['url'] ); ?>"><?php echo esc_html( $ctas[0]['label'] ); ?></a>
			<button class="sticky-cta-close" type="button" data-sticky-close>
				<span class="sr-only"><?php esc_html_e( 'Sluiten', 'react2u' ); ?></span>
				<?php echo react2u_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
<?php endif; ?>
