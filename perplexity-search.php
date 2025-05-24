<?php
/**
 * Plugin Name: Perplexity Search Integration
 * Plugin URI: https://mexiconewsdaily.com
 * Description: Integrates Perplexity's Sonar API search functionality into WordPress.
 * Version: 1.0.0
 * Author: Tim Roman
 * Author URI: https://mexiconewsdaily.com
 * Text Domain: perplexity-search
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PERPLEXITY_SEARCH_VERSION', '1.0.0');
define('PERPLEXITY_SEARCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PERPLEXITY_SEARCH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PERPLEXITY_SEARCH_DOMAIN', 'mexiconewsdaily.com');

// Include required files
require_once PERPLEXITY_SEARCH_PLUGIN_DIR . 'includes/admin-settings.php';
require_once PERPLEXITY_SEARCH_PLUGIN_DIR . 'includes/shortcode-handler.php';

// Initialize the plugin
function perplexity_search_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('perplexity-search', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Register and enqueue CSS and JS
    add_action('wp_enqueue_scripts', 'perplexity_search_enqueue_scripts');
}
add_action('plugins_loaded', 'perplexity_search_init');

/**
 * Register and enqueue scripts and styles
 */
function perplexity_search_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style(
        'perplexity-search-css',
        PERPLEXITY_SEARCH_PLUGIN_URL . 'assets/css/perplexity-search.css',
        array(),
        PERPLEXITY_SEARCH_VERSION
    );

    // Enqueue JS
    wp_enqueue_script(
        'perplexity-search-js',
        PERPLEXITY_SEARCH_PLUGIN_URL . 'assets/js/perplexity-search.js',
        array('jquery'),
        PERPLEXITY_SEARCH_VERSION,
        true
    );

    // Add data for the JavaScript
    wp_localize_script(
        'perplexity-search-js',
        'perplexitySearchData',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('perplexity-search-nonce'),
        )
    );
}

/**
 * Plugin activation hook
 */
function perplexity_search_activate() {
    // Add default options
    add_option('perplexity_search_api_key', '');
    add_option('perplexity_search_model', 'sonar');
    add_option('perplexity_search_temperature', 0.7);
    add_option('perplexity_search_top_p', 0.9);
}
register_activation_hook(__FILE__, 'perplexity_search_activate');

/**
 * Plugin deactivation hook
 */
function perplexity_search_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'perplexity_search_deactivate');

/**
 * Plugin uninstall hook
 */
function perplexity_search_uninstall() {
    // Remove options
    delete_option('perplexity_search_api_key');
    delete_option('perplexity_search_model');
    delete_option('perplexity_search_temperature');
    delete_option('perplexity_search_top_p');
}
register_uninstall_hook(__FILE__, 'perplexity_search_uninstall');

/**
 * AJAX handler for Perplexity search
 */
function perplexity_search_ajax_handler() {
    // Check nonce for security
    check_ajax_referer('perplexity-search-nonce', 'nonce');

    // Get search query
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    if (empty($query)) {
        wp_send_json_error(array('message' => __('Search query cannot be empty', 'perplexity-search')));
        return;
    }

    // Get API key from options
    $api_key = get_option('perplexity_search_api_key', '');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => __('Perplexity API key is not configured', 'perplexity-search')));
        return;
    }

    // Get model and parameters
    $model = get_option('perplexity_search_model', 'sonar');
    $temperature = get_option('perplexity_search_temperature', 0.7);
    $top_p = get_option('perplexity_search_top_p', 0.9);

    // Prepare request to Perplexity API
    $response = perplexity_search_query_api($query, $api_key, $model, $temperature, $top_p);
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
        return;
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_perplexity_search', 'perplexity_search_ajax_handler');
add_action('wp_ajax_nopriv_perplexity_search', 'perplexity_search_ajax_handler');

/**
 * Get WordPress posts based on Perplexity response
 */
function get_related_wordpress_posts($chosen_ids) {
    $posts = array();
    
    foreach ($chosen_ids as $post_id) {
        $post = get_post($post_id);
        if ($post) {
            $excerpt = wp_trim_words(get_the_excerpt($post), 15, '...');
            $featured_image = get_the_post_thumbnail_url($post, 'medium');
            
            $posts[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => $excerpt,
                'featured_image' => $featured_image,
                'date' => get_the_date('F j, Y', $post),
                'url' => get_permalink($post)
            );
        }
    }
    
    return $posts;
}

/**
 * Query the Perplexity API
 */
function perplexity_search_query_api($query, $api_key, $model = 'sonar', $temperature = 0.7, $top_p = 0.9) {
    // API endpoint
    $api_url = 'https://api.perplexity.ai/chat/completions';
    
    // Prepare the request body
    $body = array(
        'model' => $model,
        'messages' => array(
            array(
                'role' => 'system',
                'content' => 'You are a search assistant for the Mexico News Daily website. Return your response as a valid JSON object with this exact structure, and nothing else before or after the JSON:
{
    "summary": "A clear and concise summary of the current events and news, without any citations or URLs",
    "articles": [
        {
            "title": "The exact title of the article",
            "url": "The full URL of the article",
            "snippet": "A brief description of the article content",
            "image_url": "The full URL of the article\'s featured image or thumbnail. REQUIRED - do not return null. If no image is available, return a relevant placeholder image URL.",
            "date": "The publication date of the article in format: Month Day, Year"
        }
    ]
}'
            ),
            array(
                'role' => 'user',
                'content' => $query
            )
        ),
        'temperature' => (float) $temperature,
        'top_p' => (float) $top_p,
        'search_domain_filter' => array(PERPLEXITY_SEARCH_DOMAIN),
        'max_tokens' => 1000,
        'return_images' => true
    );
    
    // Prepare the request arguments
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode($body),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 45
    );
    
    // Make the request
    $response = wp_remote_post($api_url, $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('Perplexity API Error: ' . $response->get_error_message());
        return $response;
    }
    
    // Get the response code
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code !== 200) {
        $error_message = wp_remote_retrieve_response_message($response);
        error_log('Perplexity API HTTP Error: ' . $response_code . ' - ' . $error_message);
        error_log('Response Body: ' . wp_remote_retrieve_body($response));
        return new WP_Error('api_error', 'API Error (' . $response_code . '): ' . $error_message);
    }
    
    // Get and decode the response body
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Perplexity API JSON Error: ' . json_last_error_msg());
        error_log('Response Body: ' . $response_body);
        return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
    }
    
    // Try to parse the content as JSON
    $formatted_response = array(
        'choices' => array(),
        'citations' => array()
    );
    
    if (isset($response_data['choices'][0]['message']['content'])) {
        try {
            $content = $response_data['choices'][0]['message']['content'];
            // Try to parse the content as JSON
            $parsed_content = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed_content['summary'])) {
                // If successfully parsed as JSON
                $formatted_response['choices'] = array(
                    array(
                        'message' => array(
                            'content' => $parsed_content['summary']
                        )
                    )
                );
                
                if (isset($parsed_content['articles']) && is_array($parsed_content['articles'])) {
                    $formatted_response['citations'] = $parsed_content['articles'];
                }
            } else {
                // Fallback to text parsing if JSON parsing fails
                $formatted_response['choices'] = array(
                    array(
                        'message' => array(
                            'content' => $content
                        )
                    )
                );
            }
        } catch (Exception $e) {
            error_log('Error parsing Perplexity response content: ' . $e->getMessage());
            $formatted_response['choices'] = array(
                array(
                    'message' => array(
                        'content' => $content
                    )
                )
            );
        }
    }
    
    return $formatted_response;
}
