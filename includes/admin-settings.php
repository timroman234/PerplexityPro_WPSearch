<?php
/**
 * Admin settings for Perplexity Search Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu item
 */
function perplexity_search_add_admin_menu() {
    add_options_page(
        __('Perplexity Search Settings', 'perplexity-search'),
        __('Perplexity Search', 'perplexity-search'),
        'manage_options',
        'perplexity-search',
        'perplexity_search_settings_page'
    );
}
add_action('admin_menu', 'perplexity_search_add_admin_menu');

/**
 * Register settings
 */
function perplexity_search_register_settings() {
    // Register settings
    register_setting(
        'perplexity_search_options',
        'perplexity_search_api_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        )
    );
    
    register_setting(
        'perplexity_search_options',
        'perplexity_search_model',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'sonar',
        )
    );
    
    register_setting(
        'perplexity_search_options',
        'perplexity_search_temperature',
        array(
            'type' => 'number',
            'sanitize_callback' => 'perplexity_search_sanitize_float',
            'default' => 0.7,
        )
    );
    
    register_setting(
        'perplexity_search_options',
        'perplexity_search_top_p',
        array(
            'type' => 'number',
            'sanitize_callback' => 'perplexity_search_sanitize_float',
            'default' => 0.9,
        )
    );
    
    // Register setting sections
    add_settings_section(
        'perplexity_search_section_api',
        __('API Settings', 'perplexity-search'),
        'perplexity_search_section_api_callback',
        'perplexity-search'
    );
    
    add_settings_section(
        'perplexity_search_section_search',
        __('Search Settings', 'perplexity-search'),
        'perplexity_search_section_search_callback',
        'perplexity-search'
    );
    
    // Add settings fields
    add_settings_field(
        'perplexity_search_api_key',
        __('API Key', 'perplexity-search'),
        'perplexity_search_api_key_callback',
        'perplexity-search',
        'perplexity_search_section_api'
    );
    
    add_settings_field(
        'perplexity_search_model',
        __('Model', 'perplexity-search'),
        'perplexity_search_model_callback',
        'perplexity-search',
        'perplexity_search_section_search'
    );
    
    add_settings_field(
        'perplexity_search_temperature',
        __('Temperature', 'perplexity-search'),
        'perplexity_search_temperature_callback',
        'perplexity-search',
        'perplexity_search_section_search'
    );
    
    add_settings_field(
        'perplexity_search_top_p',
        __('Top P', 'perplexity-search'),
        'perplexity_search_top_p_callback',
        'perplexity-search',
        'perplexity_search_section_search'
    );
}
add_action('admin_init', 'perplexity_search_register_settings');

/**
 * Sanitize float values
 */
function perplexity_search_sanitize_float($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Section callbacks
 */
function perplexity_search_section_api_callback() {
    echo '<p>' . __('Enter your Perplexity API key and settings below. You can get your API key from the Perplexity website.', 'perplexity-search') . '</p>';
    echo '<p><a href="https://docs.perplexity.ai/home" target="_blank">' . __('Perplexity API Documentation', 'perplexity-search') . '</a></p>';
}

function perplexity_search_section_search_callback() {
    echo '<p>' . __('Configure search settings below. These settings control how the Perplexity search works.', 'perplexity-search') . '</p>';
}

/**
 * Field callbacks
 */
function perplexity_search_api_key_callback() {
    $api_key = get_option('perplexity_search_api_key', '');
    
    echo '<input type="password" id="perplexity_search_api_key" name="perplexity_search_api_key" value="' . esc_attr($api_key) . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">' . __('Your Perplexity API key. This is used to authenticate requests to the Perplexity API.', 'perplexity-search') . '</p>';
}

function perplexity_search_model_callback() {
    $model = get_option('perplexity_search_model', 'sonar');
    $models = array(
        'sonar' => __('Sonar (Standard)', 'perplexity-search'),
        'sonar-pro' => __('Sonar Pro (Advanced)', 'perplexity-search'),
        'sonar-deep-research' => __('Sonar Deep Research', 'perplexity-search'),
    );
    
    echo '<select id="perplexity_search_model" name="perplexity_search_model">';
    foreach ($models as $key => $label) {
        $selected = selected($model, $key, false);
        echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __('The Perplexity model to use for search queries.', 'perplexity-search') . '</p>';
}

function perplexity_search_temperature_callback() {
    $temperature = get_option('perplexity_search_temperature', 0.7);
    
    echo '<input type="range" id="perplexity_search_temperature" name="perplexity_search_temperature" min="0" max="1" step="0.1" value="' . esc_attr($temperature) . '" />';
    echo '<span id="perplexity_search_temperature_value">' . esc_html($temperature) . '</span>';
    echo '<p class="description">' . __('Controls randomness: 0 is more deterministic, 1 is more creative.', 'perplexity-search') . '</p>';
    
    // Add JavaScript to update displayed value
    echo '<script>
        document.getElementById("perplexity_search_temperature").addEventListener("input", function() {
            document.getElementById("perplexity_search_temperature_value").textContent = this.value;
        });
    </script>';
}

function perplexity_search_top_p_callback() {
    $top_p = get_option('perplexity_search_top_p', 0.9);
    
    echo '<input type="range" id="perplexity_search_top_p" name="perplexity_search_top_p" min="0" max="1" step="0.1" value="' . esc_attr($top_p) . '" />';
    echo '<span id="perplexity_search_top_p_value">' . esc_html($top_p) . '</span>';
    echo '<p class="description">' . __('Nucleus sampling: 0.9 considers top 90% of probability mass.', 'perplexity-search') . '</p>';
    
    // Add JavaScript to update displayed value
    echo '<script>
        document.getElementById("perplexity_search_top_p").addEventListener("input", function() {
            document.getElementById("perplexity_search_top_p_value").textContent = this.value;
        });
    </script>';
}

/**
 * Settings page
 */
function perplexity_search_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Show admin notices
    settings_errors('perplexity_search_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('perplexity_search_options');
            do_settings_sections('perplexity-search');
            submit_button();
            ?>
        </form>
        
        <hr>
        
        <h2><?php _e('Shortcode Usage', 'perplexity-search'); ?></h2>
        <p><?php _e('Use the following shortcode to add the Perplexity search to any post or page:', 'perplexity-search'); ?></p>
        <code>[perplexity_search]</code>
        
        <p><?php _e('You can also customize the search box with the following attributes:', 'perplexity-search'); ?></p>
        <ul>
            <li><code>placeholder</code> - <?php _e('Custom placeholder text for the search box', 'perplexity-search'); ?></li>
            <li><code>button_text</code> - <?php _e('Custom text for the search button', 'perplexity-search'); ?></li>
            <li><code>width</code> - <?php _e('Width of the search box (e.g., 100%, 500px)', 'perplexity-search'); ?></li>
        </ul>
        
        <p><?php _e('Example with custom attributes:', 'perplexity-search'); ?></p>
        <code>[perplexity_search placeholder="Search Mexico News Daily..." button_text="Search" width="100%"]</code>
    </div>
    <?php
}
