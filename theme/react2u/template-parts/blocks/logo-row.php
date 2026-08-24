<?php
/**
 * Rij met woordmerken (koppelingen, vervoerders, keurmerken).
 *
 * Bewust als tekst: logo's van derden mogen pas mee zodra de klant de bestanden
 * mét toestemming aanlevert. Zo staat er nu al iets dat klopt.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$items = (array) ( $args['items'] ?? array() );
if ( ! $items ) {
	return;
}
$label = (string) ( $args['label'] ?? '' );
?>
<div class="logo-row">
	<?php if ( '' !== $label ) : ?>
		<p class="logo-row-label"><?php echo react2u_text( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>
	<ul class="logo-row-list">
		<?php foreach ( $items as $item ) : ?>
			<li class="logo-chip"><?php echo esc_html( $item ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
