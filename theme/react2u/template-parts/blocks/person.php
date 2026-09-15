<?php
/**
 * Persoonskaart: het gezicht bij de dienst.
 *
 * Zonder portret tonen we een gemarkeerd vlak in plaats van een willekeurig
 * gezicht. Een verzonnen persoon naast een echt telefoonnummer betekent dat
 * iemand straks vraagt naar iemand die niet bestaat.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$person = (array) ( $args['person'] ?? array() );
if ( ! $person ) {
	return;
}

$layout = (string) ( $args['layout'] ?? 'card' );
$phone  = (bool) ( $args['phone'] ?? true );
$photo  = (string) ( $person['photo'] ?? '' );
$name   = (string) ( $person['name'] ?? '' );
?>
<div class="person is-<?php echo esc_attr( $layout ); ?>">
	<span class="person-photo">
		<?php if ( '' !== $photo ) : ?>
			<img <?php echo react2u_quality_image_attrs( str_starts_with( $photo, 'http' ) ? $photo : react2u_image_url( $photo ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				alt="<?php echo esc_attr( trim( str_replace( REACT2U_PLACEHOLDER, '', $name ) ) ); ?>"
				width="72" height="72" loading="lazy" fetchpriority="low" decoding="async">
		<?php else : ?>
			<span class="person-photo-empty" aria-hidden="true"></span>
		<?php endif; ?>
	</span>

	<span class="person-body">
		<strong class="person-name"><?php echo react2u_text( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
		<span class="person-role"><?php echo react2u_text( $person['role'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

		<?php if ( $phone && react2u_has_phone() ) : ?>
			<a class="person-phone" href="tel:<?php echo esc_attr( react2u_phone_link() ); ?>">
				<?php echo react2u_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo react2u_text( react2u_get( 'contact.phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $person['note'] ) ) : ?>
			<span class="person-note"><?php echo react2u_text( $person['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
	</span>
</div>
