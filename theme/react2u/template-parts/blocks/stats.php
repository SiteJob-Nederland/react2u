<?php
/**
 * Cijferrij / KPI-tegels. @package React2u
 *
 * @var array<string,mixed> $args
 */
$items = (array) ( $args['items'] ?? array() );
if ( ! $items ) {
	return;
}
$tone    = (string) ( $args['tone'] ?? 'light' );
$compact = (bool) ( $args['compact'] ?? false );
$notes   = (bool) ( $args['notes'] ?? false );
?>
<dl class="stat-row tone-<?php echo esc_attr( $tone ); ?><?php echo $compact ? ' is-compact' : ''; ?>">
	<?php foreach ( $items as $item ) : ?>
		<div class="stat">
			<dt class="stat-value"><?php echo react2u_text( $item['value'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dt>
			<dd class="stat-label">
				<?php echo react2u_text( $item['label'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $notes && ! empty( $item['note'] ) ) : ?>
					<span class="stat-note"><?php echo react2u_text( $item['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
			</dd>
		</div>
	<?php endforeach; ?>
</dl>
