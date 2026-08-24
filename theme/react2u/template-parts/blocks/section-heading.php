<?php
/**
 * Sectiekop met eyebrow. @package React2u
 *
 * @var array<string,mixed> $args
 */
$eyebrow = (string) ( $args['eyebrow'] ?? '' );
$title   = (string) ( $args['title'] ?? '' );
$text    = (string) ( $args['text'] ?? '' );
$level   = in_array( $args['level'] ?? 'h2', array( 'h2', 'h3' ), true ) ? $args['level'] : 'h2';
$id      = (string) ( $args['id'] ?? '' );
$align   = (string) ( $args['align'] ?? 'left' );
$tone    = (string) ( $args['tone'] ?? 'light' );
?>
<div class="section-heading is-<?php echo esc_attr( $align ); ?> tone-<?php echo esc_attr( $tone ); ?>">
	<?php if ( '' !== $eyebrow ) : ?>
		<p class="eyebrow"><span class="eyebrow-dot" aria-hidden="true"></span><?php echo react2u_text( $eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<?php if ( '' !== $title ) : ?>
		<<?php echo esc_attr( $level ); ?><?php echo '' !== $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?> class="section-title">
			<?php echo react2u_text( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo esc_attr( $level ); ?>>
	<?php endif; ?>

	<?php if ( '' !== $text ) : ?>
		<p class="section-text"><?php echo react2u_text( $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>
</div>
