<?php

$date_class = 'has-date';
if ( get_theme_mod( 'date' ) == 'no' ) {
	$date_class = 'no-date';
}

echo "<div class='site-title ". esc_attr($date_class)  ."'>";
	if ( $args['source'] == 'footer' && get_theme_mod('footer_logo') ) { ?>
		<a class="footer-logo" href="<?php echo esc_url( home_url() ); ?>">
			<img src="<?php echo esc_url(get_theme_mod('footer_logo')); ?>" />
			<span class="screen-reader-text"><?php echo esc_html( get_bloginfo('name') );  ?></span>
		</a><?php
	} elseif ( $args['source'] == 'footer' && has_custom_logo() ) {
		the_custom_logo();
	} elseif ( $args['source'] == 'header' && has_custom_logo() ) {
		the_custom_logo();
	} else {
		echo "<a href='" . esc_url( home_url() ) . "'>";
		bloginfo( 'name' );
		echo "</a>";
	}

	// "AI-Driven" badge beside the header wordmark. Header only; can be hidden via
	// the `ai_badge` theme mod (default on) and the label is filterable.
	if ( $args['source'] == 'header' && get_theme_mod( 'ai_badge', 'yes' ) != 'no' ) {
		$ai_badge_label = apply_filters( 'ct_mission_news_ai_badge_label', esc_html__( 'AI-Driven', 'mission-news' ) );
		echo '<span class="ai-badge"><span class="ai-badge__dot" aria-hidden="true"></span>' . esc_html( $ai_badge_label ) . '</span>';
	}
echo "</div>";