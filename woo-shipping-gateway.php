<?php
/**
 * Plugin Name: WooCommerce Aymakan Shipping
 * Plugin URI:
 * Description: WooCommerce Aymakan Shipping Carrier
 * Author: Aymakan
 * Author URI: https://www.aymakan.com.sa
 * Version: 2.0
 * License: GPLv2 or later
 * Text Domain: woo-aymakan-shipping
 * Domain Path: languages/
 * Developer: Abdul Shakoor Kakar
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('AYMAKAN_PATH', plugin_dir_path(__FILE__));
define('AYMAKAN_BASE', plugin_basename(__FILE__));

if (!class_exists('Aymakan_Main')) :
    /**
     * Aymakan main class.
     */
    class Aymakan_Main
    {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '1.3.0';

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        /**
         * Initialize the plugin
         */
        private function __construct()
        {
            add_action('init', array($this, 'load_plugin_textdomain'), -1);
            // Checks with WooCommerce is installed.
            if (class_exists('WC_Integration')) {
                $prefix = is_network_admin() ? 'network_admin_' : '';
                add_filter("{$prefix}plugin_action_links_" . AYMAKAN_BASE, array(&$this, 'plugin_links'));
                require AYMAKAN_PATH . 'includes/class-aymakan-shipping-helper.php';
                require AYMAKAN_PATH . 'includes/class-aymakan-shipping-form.php';
                require AYMAKAN_PATH . 'includes/class-aymakan-shipping-method.php';
                require AYMAKAN_PATH . 'includes/class-aymakan-shipping-create.php';
                add_filter('woocommerce_shipping_methods', array($this, 'aymakan_add_method'));
            } else {
                add_action('admin_notices', array($this, 'wc_aymakan_woocommerce_fallback_notice'));
            }

        }

        /**
         * Return an instance of this class.
         *
         * @return object A single instance of this class.
         */
        public static function get_instance()
        {
            // If the single instance hasn't been set, set it now.
            if (null === self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * @param $links
         *
         * @return string
         */
        public function plugin_links($links)
        {
            $more_links[] = '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=aymakan">' . __('Configure', 'aymakan') . '</a>';
            $links        = $more_links + $links;
            return $links;
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function load_plugin_textdomain()
        {
            load_plugin_textdomain('aymakan', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Add the Aymakan to shipping methods.
         *
         * @param array $methods
         *
         * @return array
         */
        function aymakan_add_method($methods)
        {
            $methods['aymakan'] = 'Aymakan_Shipping_Method';
            return $methods;
        }

    }

    add_action('plugins_loaded', array('Aymakan_Main', 'get_instance'));

endif;
