<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Aymakan_Shipping_Method
 */
class Aymakan_Shipping_Method extends WC_Shipping_Method
{
    /**
     * @var string
     */
    public $api_key = '';
    /**
     * @var string
     */
    public $test_mode = 'yes';

    /**
     * @var string
     */
    public $log = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $address = '';

    /**
     * @var string
     */
    public $region = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $cod_fee = 0;

    /**
     * @var string
     */
    public $debug = 'yes';

    /**
     * @var string
     */
    public $country = 'SA';

    /**
     * Initialize the Aymakan shipping method.
     *
     * @return void
     */
    public function __construct($instance_id = 0)
    {
        $this->id           = 'aymakan';
        $this->instance_id  = absint($instance_id);
        $this->method_title = __('Aymakan Shipping', 'woo-aymakan-shipping');
        $this->init();
    }

    /**
     * Initializes the method.
     *
     * @return void
     */
    public function init()
    {
        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->enabled   = $this->get_option('enabled');
        $this->api_key   = $this->get_option('api_key');
        $this->test_mode = $this->get_option('test_mode');
        $this->title     = $this->get_option('title');
        $this->name      = $this->get_option('collection_name');
        $this->email     = $this->get_option('collection_email');
        $this->city      = $this->get_option('collection_city');
        $this->address   = $this->get_option('collection_address');
        $this->region    = $this->get_option('collection_region');
        $this->phone     = $this->get_option('collection_phone');
        $this->cod_fee   = $this->get_option('cod_fee');
        // $this->country     = $this->get_option('collection_country');
        $this->debug = $this->get_option('debug');

        // Actions
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

        // Add action If COD fee added
        if (!empty($this->cod_fee)) {
            add_action('woocommerce_cart_calculate_fees', array($this, 'aymakan_add_cod_fee'), 10, 4);
        }
    }

    /**
     * Admin options fields.
     *
     * @return void
     */
    public function init_form_fields()
    {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-aymakan-shipping'),
                'type' => 'checkbox',
                'label' => __('Enable this shipping method', 'woo-aymakan-shipping'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => __('Aymakan', 'woo-aymakan-shipping')
            ),
            'api_key' => array(
                'title' => __('API Key', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('The API key is available at Aymakan account in Integrations.', 'woo-aymakan-shipping'),
                'desc_tip' => true
            ),
            'collection_name' => array(
                'title' => __('Collection Name', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('The collection name or any data below is related to your warehouse contact information. Here your dispatchers details can be provided.', 'woo-aymakan-shipping'),
                'desc_tip' => true
            ),
            'collection_email' => array(
                'title' => __('Collection Email', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('The collection email or any data below is related to your warehouse contact information. Here your dispatchers details can be provided.', 'woo-aymakan-shipping'),
                'desc_tip' => true
            ),
            'collection_phone' => array(
                'title' => __('Collection Phone', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('Phone number for warehouse contact information.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => ''
            ),
            'collection_address' => array(
                'title' => __('Collection Address', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('The address from which Aymakan will be picking up the shipment.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => ''
            ),
            'collection_city' => array(
                'title' => __('Collection City', 'woo-aymakan-shipping'),
                'type' => 'select',
                'description' => __('The city from which Aymakan will be picking up the shipment.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => 'Riyadh',
                'options' => Aymakan_Shipping_Helper::get_cities('en')
            ),
            'collection_region' => array(
                'title' => __('Collection Region', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('The region from which Aymakan will be picking up the shipment.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => ''
            ),
            'cod_fee' => array(
                'title' => __('Add COD Fee', 'woo-aymakan-shipping'),
                'type' => 'text',
                'description' => __('Add cash on deliver fee.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => '0'
            ),
            'testing' => array(
                'title' => __('Testing', 'woo-aymakan-shipping'),
                'type' => 'title'
            ),
            'test_mode' => array(
                'title' => __('Test Mode', 'woo-aymakan-shipping'),
                'type' => 'checkbox',
                'description' => __('Check the checkbox for enabling test mode.', 'woo-aymakan-shipping'),
                'desc_tip' => true,
                'default' => 'no'
            ),
            'debug' => array(
                'title' => __('Debug Log', 'woo-aymakan-shipping'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'woo-aymakan-shipping'),
                'default' => 'no',
                'description' => sprintf(__('Log Aymakan events, such as WebServices requests, inside %s.', 'woo-aymakan-shipping'), '<code>woocommerce/logs/aymakan-' . sanitize_file_name(wp_hash('aymakan')) . '.txt</code>')
            )
        );
        $this->form_fields          = $this->instance_form_fields;
    }

    /**
     * Aymakan options page.
     *
     * @return void
     */
    public function admin_options()
    {
        echo '<h3>' . $this->method_title . '</h3>';
        echo '<p>' . __('Aymakan is a Saudi Arabia based courier service.', 'woo-aymakan-shipping') . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
    }

    /**
     * Checks if the method is available.
     *
     * @param array $package Order package.
     *
     * @return bool
     */
    public function is_available($package)
    {
        $is_available = true;
        if ('no' == $this->enabled) {
            $is_available = false;
        }
        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package);
    }

    /**
     * Add cash on deliver fee
     */
    function aymakan_add_cod_fee()
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            if (empty($this->cod_fee))
                return;
        }

        $chosen_gateway = WC()->session->chosen_payment_method;

        if ($chosen_gateway == 'cod') {
            WC()->cart->add_fee(__('COD fee', 'woo-aymakan-shipping'), $this->cod_fee, false, '');
        }
    }

}
