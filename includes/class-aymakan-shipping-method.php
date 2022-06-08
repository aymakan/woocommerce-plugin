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
    public $neighbourhood = '';

    /**
     * @var string
     */
    public $phone = '';

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
        $this->method_title = __('Aymakan Shipping', 'aymakan');
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
        $this->enabled       = $this->get_option('enabled');
        $this->api_key       = $this->get_option('api_key');
        $this->test_mode     = $this->get_option('test_mode');
        $this->title         = $this->get_option('title');
        $this->name          = $this->get_option('collection_name');
        $this->email         = $this->get_option('collection_email');
        $this->city          = $this->get_option('collection_city');
        $this->address       = $this->get_option('collection_address');
        $this->neighbourhood = $this->get_option('collection_neighbourhood');
        $this->phone         = $this->get_option('collection_phone');
        // $this->country     = $this->get_option('collection_country');
        $this->debug = $this->get_option('debug');

        // Active logs.
        if ('yes' === $this->debug) {
            if (class_exists('WC_Logger')) {
                $this->log = new WC_Logger();
            } else {
                $this->log = $this->woocommerce_method()->logger();
            }
        }

        // Actions
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
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
                'title' => __('Enable/Disable', 'aymakan'),
                'type' => 'checkbox',
                'label' => __('Enable this shipping method', 'aymakan'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'aymakan'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'aymakan'),
                'desc_tip' => true,
                'default' => __('Aymakan', 'aymakan')
            ),
            'api_key' => array(
                'title' => __('API Key', 'aymakan'),
                'type' => 'text',
                'description' => __('The API key is available at Aymakan account in Integrations.', 'aymakan'),
                'desc_tip' => true
            ),
            'collection_name' => array(
                'title' => __('Collection Name', 'aymakan'),
                'type' => 'text',
                'description' => __('The collection name or any data below is related to your warehouse contact information. Here your dispatchers details can be provided.', 'aymakan'),
                'desc_tip' => true
            ),
            'collection_email' => array(
                'title' => __('Collection Email', 'aymakan'),
                'type' => 'text',
                'description' => __('The collection email or any data below is related to your warehouse contact information. Here your dispatchers details can be provided.', 'aymakan'),
                'desc_tip' => true
            ),
            'collection_phone' => array(
                'title' => __('Collection Phone', 'aymakan'),
                'type' => 'text',
                'description' => __('Phone number for warehouse contact information.', 'aymakan'),
                'desc_tip' => true,
                'default' => ''
            ),
            'collection_address' => array(
                'title' => __('Collection Address', 'aymakan'),
                'type' => 'text',
                'description' => __('The address from which Aymakan will be picking up the shipment.', 'aymakan'),
                'desc_tip' => true,
                'default' => ''
            ),
            'collection_city' => array(
                'title' => __('Collection City', 'aymakan'),
                'type' => 'select',
                'description' => __('The city from which Aymakan will be picking up the shipment.', 'aymakan'),
                'desc_tip' => true,
                'default' => 'Riyadh',
                'options' => Aymakan_Shipping_Helper::get_cities()
            ),
            'collection_neighbourhood' => array(
                'title' => __('Collection Neighbourhood', 'aymakan'),
                'type' => 'text',
                'description' => __('The neighbourhood from which Aymakan will be picking up the shipment.', 'aymakan'),
                'desc_tip' => true,
                'default' => ''
            ),
            'testing' => array(
                'title' => __('Testing', 'aymakan'),
                'type' => 'title'
            ),
            'test_mode' => array(
                'title' => __('Test Mode', 'aymakan'),
                'type' => 'checkbox',
                'description' => __('Check the checkbox for enabling test mode.', 'aymakan'),
                'desc_tip' => true,
                'default' => 'no'
            ),
            'debug' => array(
                'title' => __('Debug Log', 'aymakan'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'aymakan'),
                'default' => 'no',
                'description' => sprintf(__('Log Aymakan events, such as WebServices requests, inside %s.', 'aymakan'), '<code>woocommerce/logs/aymakan-' . sanitize_file_name(wp_hash('aymakan')) . '.txt</code>')
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
        echo '<p>' . __('Aymakan is a Saudi Arabia based courier service.', 'aymakan') . '</p>';
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
        if ('no' === $this->enabled) {
            $is_available = false;
        }
        return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package);
    }

}
