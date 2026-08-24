<?php
/**
 * Casekaart: branche, resultaatcijfer, korte tekst, link. @package React2u
 *
 * @var array<string,mixed> $args
 */
$case = (array) ( $args['case'] ?? array() );
if ( ! $case ) {
	return;
}
$url = (string) ( $case['url'] ?? '' );
?>
<article class="case-card">
	<p class="case-sector"><?php echo react2u_text( $case['sector'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

	<p class="case-metric">
		<span class="case-metric-value"><?php echo react2u_text( $case['metric'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="case-metric-label"><?php echo react2u_text( $case['metric_label'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</p>

	<h3 class="case-title"><?php echo react2u_text( $case['title'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
	<p class="case-text"><?php echo react2u_text( $case['text'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

	<?php if ( '' !== $url ) : ?>
		<a class="arrow-link" href="<?php echo esc_url( $url ); ?>">
			<?php esc_html_e( 'Lees de case', 'react2u' ); ?> <?php echo react2u_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	<?php endif; ?>
</article>
