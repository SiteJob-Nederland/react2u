<?php
/**
 * CTA-blok in vier varianten en vier vormen. @package React2u
 *
 * layout: section (volle sectie) | inline (in een artikel) | aside (zijkolom) | bar (sticky balk)
 * tone:   dark | light | soft
 *
 * @var array<string,mixed> $args
 */
$layout    = (string) ( $args['layout'] ?? 'section' );
$tone      = (string) ( $args['tone'] ?? 'dark' );
$variant   = (string) ( $args['variant'] ?? 'quote' );
$eyebrow   = (string) ( $args['eyebrow'] ?? '' );
$title     = (string) ( $args['title'] ?? '' );
$text      = (string) ( $args['text'] ?? '' );
$primary   = (string) ( $args['primary'] ?? 'quote' );
$secondary = $args['secondary'] ?? '';
$heading   = in_array( $args['heading'] ?? 'h2', array( 'h2', 'h3', 'p' ), true ) ? (string) $args['heading'] : 'h2';
$show_rate = (bool) ( $args['rating'] ?? false );

$rating_markup = '';
if ( $show_rate ) {
	ob_start();
	react2u_rating_badge( array( 'size' => 'small', 'compact' => in_array( $layout, array( 'aside', 'bar' ), true ) ) );
	$rating_markup = trim( (string) ob_get_clean() );
}

$person_markup = '';
if ( ! empty( $args['person'] ) ) {
	$person_args = array( 'layout' => 'inline' );
	if ( is_array( $args['person'] ) ) {
		$person_args['person'] = $args['person'];
	}

	ob_start();
	react2u_person( $person_args );
	$person_markup = trim( (string) ob_get_clean() );
}

$primary_class = 'dark' === $tone ? 'button button-white' : 'button button-primary';
$ghost_class   = 'dark' === $tone ? 'button button-outline-light' : 'button button-ghost';
?>
<div class="cta cta-<?php echo esc_attr( $layout ); ?> tone-<?php echo esc_attr( $tone ); ?> variant-<?php echo esc_attr( $variant ); ?>">
	<div class="cta-body">
		<?php if ( '' !== $eyebrow ) : ?>
			<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $title ) : ?>
			<<?php echo esc_attr( $heading ); ?> class="cta-title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $heading ); ?>>
		<?php endif; ?>

		<?php if ( '' !== $text ) : ?>
			<p class="cta-text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $person_markup ) : ?>
			<div class="cta-person"><?php echo $person_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>

		<?php if ( '' !== $rating_markup ) : ?>
			<div class="cta-rating">
				<?php echo $rating_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="cta-actions">
		<a class="<?php echo esc_attr( $primary_class ); ?>" href="<?php echo esc_url( react2u_cta_url( $primary ) ); ?>">
			<?php echo esc_html( react2u_cta_label( $primary ) ); ?>
		</a>

		<?php if ( is_string( $secondary ) && '' !== $secondary ) : ?>
			<a class="<?php echo esc_attr( $ghost_class ); ?>" href="<?php echo esc_url( react2u_cta_url( $secondary ) ); ?>">
				<?php echo esc_html( react2u_cta_label( $secondary ) ); ?>
			</a>
		<?php endif; ?>

		<?php /* Staat het nummer al bij de persoon, dan hoeft het hier niet nog een keer. */ ?>
		<?php if ( '' === $person_markup && react2u_has_phone() && in_array( $layout, array( 'aside', 'section' ), true ) ) : ?>
			<p class="cta-phone">
				<?php echo react2u_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Liever bellen?', 'react2u' ); ?>
				<a href="tel:<?php echo esc_attr( react2u_phone_link() ); ?>"><?php echo react2u_text( react2u_get( 'contact.phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</p>
		<?php endif; ?>
	</div>
</div>
