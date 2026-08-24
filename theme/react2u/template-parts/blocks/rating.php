<?php
/**
 * Sterrenscore-badge. @package React2u
 *
 * @var array<string,mixed> $args
 */
$size    = (string) ( $args['size'] ?? 'default' );
$compact = (bool) ( $args['compact'] ?? false );
$score   = (string) react2u_get( 'rating.score' );
$max     = (string) react2u_get( 'rating.max', '5' );
$count   = (string) react2u_get( 'rating.count' );
$source  = (string) react2u_get( 'rating.source' );
$url     = (string) react2u_get( 'rating.url' );
$value   = react2u_rating_value();

/*
 * Geen score, of een score die nog een placeholder is: dan tonen we niets.
 *
 * Die tweede voorwaarde is belangrijker dan hij lijkt. Zonder die regel wordt
 * "[PLACEHOLDER] 4,8" netjes uitgelezen als 4,8 en tekent dit blok vijf volle
 * sterren. De tekst is dan wel gemarkeerd, maar het sterrenrijtje ernaast ziet
 * er precies zo uit als bij een echte score — en dat is de ene plek waar een
 * placeholder níet als placeholder leest.
 *
 * Het voorbeeld ("bijv. 4,8") hoort dan ook in het Customizer-label thuis en
 * niet in de waarde zelf.
 */
if ( '' === trim( str_replace( REACT2U_PLACEHOLDER, '', $score ) ) || react2u_is_placeholder( $score ) ) {
	return;
}

$label = sprintf(
	/* translators: 1: score, 2: maximum */
	__( '%1$s van de %2$s sterren', 'react2u' ),
	trim( str_replace( REACT2U_PLACEHOLDER, '', $score ) ),
	$max
);

$tag  = '' !== $url ? 'a' : 'div';
$attr = '' !== $url ? ' href="' . esc_url( $url ) . '" rel="noopener"' : '';
?>
<<?php echo esc_attr( $tag ) . $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="rating-badge size-<?php echo esc_attr( $size ); ?><?php echo $compact ? ' is-compact' : ''; ?>">
	<span class="rating-stars" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php echo react2u_stars_markup( $value, (int) $max ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>
	<span class="rating-meta">
		<strong class="rating-score"><?php echo react2u_text( $score ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
		<?php if ( ! $compact ) : ?>
			<span class="rating-detail">
				<?php echo react2u_text( $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'beoordelingen', 'react2u' ); ?>
				<?php if ( '' !== $source ) : ?>
					· <?php echo react2u_text( $source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</span>
</<?php echo esc_attr( $tag ); ?>>
