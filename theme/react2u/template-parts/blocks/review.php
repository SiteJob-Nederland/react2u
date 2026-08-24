<?php
/**
 * Reviewblok: quote met sterren, naam en bedrijf. @package React2u
 *
 * @var array<string,mixed> $args
 */
$review = (array) ( $args['review'] ?? array() );
if ( ! $review ) {
	return;
}
$size    = (string) ( $args['size'] ?? 'default' );
$rating  = (int) ( $review['rating'] ?? 5 );
$company = trim( str_replace( REACT2U_PLACEHOLDER, '', (string) ( $review['company'] ?? '' ) ) );

/*
 * Is de quote zelf nog een placeholder, dan tekenen we er geen sterren bij.
 *
 * Zonder deze regel staat er vijf volle sterren boven een tekst die zichtbaar
 * nog ingevuld moet worden. De tekst leest dan als placeholder maar de sterren
 * niet — die zien er precies zo uit als bij een echte beoordeling. Dat is de
 * ene plek waar een placeholder ongemerkt een bewering wordt.
 */
$is_leeg = react2u_is_placeholder( $review['quote'] ?? '' );
?>
<figure class="review size-<?php echo esc_attr( $size ); ?><?php echo $is_leeg ? ' is-onbevestigd' : ''; ?>">
	<?php if ( 'feature' === $size ) : ?>
		<span class="review-mark" aria-hidden="true"><?php echo react2u_icon( 'quote', array( 'stroke' => '0' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php endif; ?>

	<?php if ( ! $is_leeg ) : ?>
		<span class="review-stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: aantal sterren */ __( '%d van de 5 sterren', 'react2u' ), $rating ) ); ?>">
			<?php echo react2u_stars_markup( (float) $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
	<?php endif; ?>

	<blockquote class="review-quote">
		<p><?php echo react2u_text( $review['quote'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	</blockquote>

	<figcaption class="review-source">
		<?php
		/*
		 * Initiaal van het bedrijf, en anders van de naam. Zonder die terugval
		 * staat er een punt in het rondje zodra een klant particulieren citeert
		 * in plaats van bedrijven — en dat doet een sportclub, een kliniek of
		 * een webshop allemaal.
		 */
		$naam_bron = '' !== $company ? $company : trim( str_replace( REACT2U_PLACEHOLDER, '', (string) ( $review['name'] ?? '' ) ) );
		?>
		<span class="review-avatar" aria-hidden="true"><?php echo esc_html( '' !== $naam_bron ? mb_strtoupper( mb_substr( $naam_bron, 0, 1 ) ) : '·' ); ?></span>
		<span class="review-who">
			<strong><?php echo react2u_text( $review['name'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
			<span>
				<?php echo react2u_text( $review['role'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( ! empty( $review['company'] ) ) : ?>
					· <?php echo react2u_text( $review['company'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</span>
		</span>
	</figcaption>
</figure>
