<?php
/**
 * FAQ-accordeon. Levert samen met inc/seo.php het FAQPage-schema.
 *
 * @package React2u
 * @var array<string,mixed> $args
 */
$html    = (string) ( $args['html'] ?? '' );
$title   = (string) ( $args['title'] ?? __( 'Veelgestelde vragen', 'react2u' ) );
$eyebrow = (string) ( $args['eyebrow'] ?? '' );

if ( '' === trim( $html ) ) {
	return;
}
?>
<section class="faq-section" aria-labelledby="faq-title">
	<?php
	react2u_section_heading(
		array(
			'eyebrow' => $eyebrow,
			'title'   => $title,
			'id'      => 'faq-title',
		)
	);
	?>
	<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup uit react2u_prepare_content, al door wp_kses_post. ?>
</section>
