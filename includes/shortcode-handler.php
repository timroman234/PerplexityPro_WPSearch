<?php
/**
 * Shortcode handler for Perplexity Search Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the perplexity_search shortcode
 */
function perplexity_search_register_shortcode() {
    add_shortcode('perplexity_search', 'perplexity_search_shortcode');
}
add_action('init', 'perplexity_search_register_shortcode');

/**
 * Callback function for the perplexity_search shortcode
 */
function perplexity_search_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'placeholder' => 'Search articles...',
    ), $atts);
    
    // This is the message to show if user is not a Subscriber for the Perplexity Search utility
    $perplexitySearchNotification = '<div id="loggedOutCommentContainer" align="center">
    <div id="commentText" class="loggedOutCommentText" style="align:center">You need to be a Paid Subscriber to use the Perplexity Search engine on Mexico News Daily.</div><div id="comment_button_container"><a href="/subscribe/" class="comment_subscribe_button" id="comment_subscribe_buttontext">Subscribe Today!</a></div></div>';
    
    // Check to see if the User is a Paid Subscriber and is one of these Paid Levels 
    if(leaky_paywall_has_user_paid() && leaky_paywall_subscriber_current_level_id() != "8") {
        // If user is a paid subscriber (not level 8), show the search form
        ob_start();
        ?>
        <div class="perplexity-search-container">
        <div class="pp-credits">
        <div class="pp-credits-text">Powered by Perplexity</div>
        </div> 
            <form class="perplexity-search-form">
                <input type="text" 
                       class="perplexity-search-input" 
                       placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                       aria-label="Search">
            </form>
            <div class="perplexity-search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // If user is not a paid subscriber or is level 8, show the notification
    return $perplexitySearchNotification;
}

// Register the sidebar_pp_search shortcode
function sidebar_pp_search_register_shortcode() {
    add_shortcode('sidebar_pp_search', 'sidebar_pp_search_shortcode');
}
add_action('init', 'sidebar_pp_search_register_shortcode');

// Callback for the sidebar_pp_search shortcode
function sidebar_pp_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'placeholder' => 'Ask a question or search...',
        'button_text' => 'Search',
    ), $atts);
    ob_start();
    ?>
    <form class="sidebar-pp-search-form" action="/mnd-search/" method="get">
        <input type="text" name="q" class="sidebar-pp-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" aria-label="Search" style="border: none;">
        <div class="sidebar-pp-search-buttons">
            <button type="button" class="sidebar-pp-search-clear" aria-label="Clear search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <button type="submit" class="sidebar-pp-search-submit" aria-label="Search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
