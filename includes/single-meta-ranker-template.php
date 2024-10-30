<?php
/***
 *
 *
 *  ========    EXPERIMENTAL    ==========
 *
 * Custom template for rendering shortcode preview at backend without loading WP theme
 *
 *
 *
 **/

 wp_head();
echo "<!-- THIS IS A CUSTOM TEMPLATE CREATED FOR `Meta Ranker` " . MRV_VERSION . " -->";
echo "<div style='position:relative;width:100%; padding-top: 15px;'>";

echo do_shortcode('[meta-ranker id=' . esc_html($post->ID) . ']');

echo "</div>";

wp_footer();