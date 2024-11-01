<?php
/**
 * Plugin Name: Video Gallery For YouTube
 * Description: You can display YouTube channel and playlist videos on your website.
 * Version: 1.0.4
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: yt-video-gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {exit;}

// Constant
if ('localhost' === $_SERVER['HTTP_HOST']) {
    $plugin_version = time();
} else {
    $plugin_version = '1.0.4';

}
define('YTVGB_PLUGIN_VERSION', $plugin_version);

// define('YTVGB_PLUGIN_VERSION', 'localhost' === $_SERVER['HTTP_HOST']  time() : '1.0.3');
define('YTVGB_DIR', plugin_dir_url(__FILE__));
define('YTVGB_ASSETS_DIR', plugin_dir_url(__FILE__) . 'assets/');

// YouTube Video Gallery Class

class ytvgb_YouTube_Video_Gallery
{
    public function __construct()
    {
        add_action('enqueue_block_assets', [$this, 'enqueueBlockAssets']);
        add_action('init', [$this, 'onInit']);
        add_action('admin_init', [$this, 'registerytvgbSetting']);
        add_action('rest_api_init', [$this, 'registerytvgbSetting']);
    }

    public function registerytvgbSetting()
    {
        register_setting('ytvgb-video-gallery', 'ytvgb-video-gallery', array(
            'show_in_rest' => array(
                'name' => 'ytvgb-video-gallery',
                'schema' => array(
                    'type' => 'string',
                ),
            ),
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }

    public function enqueueBlockAssets()
    {
        wp_register_style('ytvgb-gallery-style', plugins_url('dist/style.css', __FILE__), [], YTVGB_PLUGIN_VERSION);
        wp_register_script('ytvgb-api', YTVGB_ASSETS_DIR . 'js/api.js', YTVGB_PLUGIN_VERSION);
        wp_register_script('ytvgb-gallery-script', YTVGB_DIR . 'dist/script.js', ['react', 'react-dom', 'ytvgb-api'], YTVGB_PLUGIN_VERSION);
    }

    public function onInit()
    {
        wp_register_style('ytvgb-gallery-editor-style', plugins_url('dist/editor.css', __FILE__), ['ytvgb-gallery-style'], YTVGB_PLUGIN_VERSION); // Backend Style

        register_block_type(__DIR__, [
            'editor_style' => 'ytvgb-gallery-editor-style',
            'render_callback' => [$this, 'render'],
        ]); // Register Block

        wp_set_script_translations('ytvgb-gallery-editor-script', 'yt-video-gallery', plugin_dir_path(__FILE__) . 'languages'); // Translate
    }

    public function render($attributes)
    {
        extract($attributes);

        $className = $className ?? '';
        $blockClassName = 'wp-block-ytvgb-gallery' . $className . ' align' . $align;

        wp_enqueue_style('ytvgb-gallery-style');
        wp_enqueue_script('ytvgb-gallery-script');

        ob_start();?>
		<div class='<?php echo esc_attr($blockClassName); ?>' id='ytvgbYouTubeVideoGallery-<?php echo esc_attr($cId) ?>' data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>' data-ytvgbInfo='<?php echo esc_attr(get_option('ytvgb-video-gallery')); ?>'></div>

		<?php return ob_get_clean();
    } // Render
}
new ytvgb_YouTube_Video_Gallery();