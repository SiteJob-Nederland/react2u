<?php
/**
 * Bannerslot: breed en dun, met terugval.
 *
 * Zonder uitgelichte afbeelding valt het blok terug op een eigen foto uit
 * inc/proof.php ('people.banners'), en anders op het beeldmerk. Een artikel dat
 * zonder beeld wordt aangeleverd mag er niet half af uitzien, maar een
 * willekeurige stockfoto is erger dan geen foto.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$post_id  = (int) ( $args['post_id'] ?? get_the_ID() );
$label    = (string) ( $args['label'] ?? '' );
$fallback = (bool) ( $args['fallback'] ?? true );

if ( has_post_thumbnail( $post_id ) ) :
	?>
	<figure class="banner has-image">
		<?php
		echo wp_get_attachment_image(
			get_post_thumbnail_id( $post_id ),
			'react2u-content-banner',
			false,
			array(
				'class'         => 'banner-image',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'sizes'         => '(max-width: 900px) 100vw, 1200px',
			)
		);
		?>
		<?php if ( '' !== $label ) : ?>
			<figcaption class="banner-label"><?php echo esc_html( $label ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
	return;
endif;

if ( ! $fallback ) {
	return;
}

/*
 * Welke terugvalfoto, hangt af van het bericht-ID — zo krijgt niet elk artikel
 * in een overzicht dezelfde.
 */
$banners = (array) react2u_get( 'people.banners', array() );
$banner  = $banners ? $banners[ $post_id % count( $banners ) ] : null;

if ( $banner && ! empty( $banner['file'] ) ) :
	?>
	<figure class="banner is-fallback has-photo">
		<img
			class="banner-image"
			<?php echo react2u_quality_image_attrs( react2u_image_url( (string) $banner['file'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			alt="<?php echo esc_attr( (string) ( $banner['alt'] ?? '' ) ); ?>"
			width="1360" height="420"
			loading="eager" fetchpriority="high" decoding="async"
			sizes="(max-width: 900px) 100vw, 1200px">
		<?php if ( '' !== $label ) : ?>
			<figcaption class="banner-label"><?php echo esc_html( $label ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
else :
	?>
	<div class="banner is-fallback" role="presentation">
		<span class="banner-mark" aria-hidden="true"><?php echo react2u_logo_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
	<?php
endif;
