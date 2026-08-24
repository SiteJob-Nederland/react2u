<?php
/**
 * Sitevoet.
 *
 * @package React2u
 */

$footer_proof_has_placeholder = static function ( mixed $value ) use ( &$footer_proof_has_placeholder ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $nested_value ) {
			if ( $footer_proof_has_placeholder( $nested_value ) ) {
				return true;
			}
		}
		return false;
	}

	if ( ! is_string( $value ) ) {
		return false;
	}

	$text = strtolower( trim( wp_strip_all_tags( $value ) ) );
	return react2u_is_placeholder( $value )
		|| 1 === preg_match( '/\[(?:placeholder|onbevestigd|unconfirmed|unverified)\]/i', $value )
		|| in_array( $text, array( 'placeholder', 'tbd', 'todo', 'n.t.b.', 'ntb', 'nog aan te leveren', 'nog in te vullen' ), true );
};

$footer_proof_is_explicitly_unconfirmed = static function ( array $item ): bool {
	foreach ( array( 'placeholder', 'is_placeholder' ) as $flag ) {
		if ( array_key_exists( $flag, $item ) && true === filter_var( $item[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	foreach ( array( 'verified', 'confirmed', 'is_verified', 'is_confirmed' ) as $flag ) {
		if ( array_key_exists( $flag, $item ) && true !== filter_var( $item[ $flag ], FILTER_VALIDATE_BOOLEAN ) ) {
			return true;
		}
	}

	$status = strtolower( trim( (string) ( $item['status'] ?? '' ) ) );
	return in_array( $status, array( '0', 'concept', 'draft', 'onbevestigd', 'pending', 'placeholder', 'todo', 'unconfirmed', 'unverified' ), true );
};

$footer_proof_is_text = static function ( mixed $value ): bool {
	if ( ! is_scalar( $value ) || react2u_is_placeholder( $value ) ) {
		return false;
	}

	$text = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $text ) {
		return false;
	}

	return ! in_array( strtolower( $text ), array( '0', 'label', 'placeholder', 'tbd', 'todo', 'n.t.b.', 'ntb', 'nog aan te leveren', 'nog in te vullen', 'toelichting van één zin.' ), true );
};

$footer_proof_is_positive_measure = static function ( mixed $value ) use ( $footer_proof_is_text ): bool {
	return $footer_proof_is_text( $value ) && 1 === preg_match( '/[1-9]/', wp_strip_all_tags( (string) $value ) );
};

$footer_stats = array();
foreach ( (array) react2u_get( 'stats', array() ) as $footer_stat_index => $footer_stat ) {
	if ( ! is_array( $footer_stat ) ) {
		continue;
	}

	/* Customizer-overrides bestaan op leaf-paden, niet op de hele stats-array. */
	$footer_stat['value'] = react2u_get( "stats.{$footer_stat_index}.value", $footer_stat['value'] ?? '' );
	$footer_stat['label'] = react2u_get( "stats.{$footer_stat_index}.label", $footer_stat['label'] ?? '' );

	if ( isset( $footer_stat['note'] ) && ! $footer_proof_is_text( $footer_stat['note'] ) ) {
		unset( $footer_stat['note'] );
	}

	if (
		! $footer_proof_has_placeholder( $footer_stat )
		&& ! $footer_proof_is_explicitly_unconfirmed( $footer_stat )
		&& $footer_proof_is_positive_measure( $footer_stat['value'] ?? '' )
		&& $footer_proof_is_text( $footer_stat['label'] ?? '' )
	) {
		$footer_stats[] = $footer_stat;
	}
}

$footer_rating = (array) react2u_get( 'rating', array() );
foreach ( array( 'score', 'max', 'count', 'source', 'url' ) as $footer_rating_key ) {
	$footer_rating[ $footer_rating_key ] = react2u_get( "rating.{$footer_rating_key}", $footer_rating[ $footer_rating_key ] ?? '' );
}

$footer_rating_score = str_replace( ',', '.', trim( (string) ( $footer_rating['score'] ?? '' ) ) );
$footer_rating_max   = str_replace( ',', '.', trim( (string) ( $footer_rating['max'] ?? '' ) ) );
$footer_rating_count = trim( (string) ( $footer_rating['count'] ?? '' ) );
$footer_rating_valid = ! $footer_proof_has_placeholder( $footer_rating )
	&& ! $footer_proof_is_explicitly_unconfirmed( $footer_rating )
	&& $footer_proof_is_text( $footer_rating_count )
	&& $footer_proof_is_text( $footer_rating['source'] ?? '' )
	&& 1 === preg_match( '/^[0-9]+(?:\.[0-9]+)?$/', $footer_rating_score )
	&& 1 === preg_match( '/^[0-9]+(?:\.[0-9]+)?$/', $footer_rating_max )
	&& (float) $footer_rating_score > 0
	&& (float) $footer_rating_max > 0
	&& (float) $footer_rating_score <= (float) $footer_rating_max
	&& 1 === preg_match( '/[1-9]/', $footer_rating_count );
?>
<footer class="site-footer">
	<?php if ( $footer_rating_valid || $footer_stats ) : ?>
		<div class="shell footer-proof">
			<?php if ( $footer_rating_valid ) : ?>
				<div class="footer-proof-rating">
					<?php react2u_rating_badge( array( 'size' => 'default', 'tone' => 'dark' ) ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $footer_stats ) : ?>
				<?php react2u_stats( array( 'items' => $footer_stats, 'tone' => 'dark', 'compact' => true ) ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="shell react-route react-route--footer" aria-hidden="true">
		<span class="react-route-signals"><i></i><i></i><i></i><i></i><i></i><i></i></span>
		<span class="react-route-line"></span>
		<span class="react-route-destination"></span>
	</div>

	<div class="shell footer-grid">
		<div class="footer-brand">
			<a class="brand brand-footer brand-on-light" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — naar de homepage', 'react2u' ), (string) get_bloginfo( 'name' ) ) ); ?>">
				<?php echo react2u_logo( array( 'width' => 210 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<p class="footer-pitch">
				<?php echo react2u_text( react2u_get( 'contact.tagline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		</div>

		<nav class="footer-column" aria-labelledby="footer-pages">
			<h2 class="footer-heading" id="footer-pages"><?php esc_html_e( 'React2u', 'react2u' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'fallback_cb'    => 'react2u_footer_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<nav class="footer-column" aria-labelledby="footer-knowledge">
			<h2 class="footer-heading" id="footer-knowledge"><?php esc_html_e( 'Kennis', 'react2u' ); ?></h2>
			<ul class="menu">
				<?php if ( post_type_exists( 'react2u_kennisbank' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'react2u_kennisbank' ) ?: home_url( '/kennisbank/' ) ); ?>"><?php esc_html_e( 'Kennisbank', 'react2u' ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( get_option( 'page_for_posts' ) ? (string) get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'react2u' ); ?></a></li>
				<?php /* Label uit proof.php, niet hardcoded: bij de ene klant heet dit "Tarieven" en bij de andere "Wat kost een advocaat?". */ ?>
				<li><a href="<?php echo esc_url( react2u_cta_url( 'pricing' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'pricing' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( react2u_cta_url( 'contact' ) ); ?>"><?php echo esc_html( react2u_cta_label( 'contact' ) ); ?></a></li>
			</ul>
		</nav>

		<div class="footer-column footer-contact">
			<h2 class="footer-heading"><?php esc_html_e( 'Contact', 'react2u' ); ?></h2>
			<address>
				<?php if ( react2u_has_phone() ) : ?>
					<a href="tel:<?php echo esc_attr( react2u_phone_link() ); ?>">
						<?php echo react2u_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo react2u_text( react2u_get( 'contact.phone' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<a href="mailto:<?php echo esc_attr( str_replace( REACT2U_PLACEHOLDER . ' ', '', (string) react2u_get( 'contact.email' ) ) ); ?>">
					<?php echo react2u_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo react2u_text( react2u_get( 'contact.email' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<span class="footer-address">
					<?php echo react2u_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span>
						<?php echo react2u_text( react2u_get( 'contact.street' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
						<?php echo react2u_text( react2u_get( 'contact.postcode' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo react2u_text( react2u_get( 'contact.city' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
						<?php echo esc_html( (string) react2u_get( 'contact.country' ) ); ?>
					</span>
				</span>
			</address>
			<p class="footer-legal-numbers">
				<?php esc_html_e( 'KvK', 'react2u' ); ?> <?php echo react2u_text( react2u_get( 'contact.kvk' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
				<?php esc_html_e( 'Btw', 'react2u' ); ?> <?php echo react2u_text( react2u_get( 'contact.vat' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		</div>
	</div>

	<div class="shell footer-bottom">
		<span>
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( (string) get_bloginfo( 'name' ) ); ?>
			<?php
			$group = (string) react2u_get( 'contact.group' );
			if ( '' !== $group ) :
				?>
				· <?php echo react2u_text( $group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</span>
		<nav class="footer-legal" aria-label="<?php esc_attr_e( 'Juridische informatie', 'react2u' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'legal',
					'container'      => false,
					'fallback_cb'    => 'react2u_legal_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
