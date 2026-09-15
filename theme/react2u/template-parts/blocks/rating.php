<?php
/**
 * Sterrenscore-badge. @package React2u
 *
 * @var array<string,mixed> $args
 */
$size    = (string) ( $args['size'] ?? 'default' );
$compact = (bool) ( $args['compact'] ?? false );
$rating  = react2u_public_rating_data();

/* Onvolledige of onbevestigde publieke ratingdata levert helemaal geen badge. */
if ( null === $rating ) {
	return;
}

$score  = $rating['score'];
$max    = $rating['max'];
$count  = $rating['count'];
$source = $rating['source'];
$url    = $rating['url'];
$value  = (float) $rating['score_normalized'];
$stars  = max( 1, (int) ceil( (float) $rating['max_normalized'] ) );

$label = sprintf(
	/* translators: 1: score, 2: maximum */
	__( '%1$s van de %2$s sterren', 'react2u' ),
	$score,
	$max
);

$tag  = '' !== $url ? 'a' : 'div';
$attr = '' !== $url ? ' href="' . esc_url( $url ) . '" rel="noopener"' : '';
?>
<<?php echo esc_attr( $tag ) . $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="rating-badge size-<?php echo esc_attr( $size ); ?><?php echo $compact ? ' is-compact' : ''; ?>">
	<span class="rating-stars" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php echo react2u_stars_markup( $value, $stars ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>
	<span class="rating-meta">
		<strong class="rating-score"><?php echo esc_html( $score ); ?></strong>
		<?php if ( ! $compact ) : ?>
			<span class="rating-detail">
				<?php echo esc_html( $count ); ?>
				<?php esc_html_e( 'beoordelingen', 'react2u' ); ?>
				· <?php echo esc_html( $source ); ?>
			</span>
		<?php endif; ?>
	</span>
</<?php echo esc_attr( $tag ); ?>>
