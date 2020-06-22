<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Aymakan_Shipping_Create
 */
class Aymakan_Shipping_Create extends Aymakan_Shipping_Method
{
    /**
     * @var string
     */
    public $endPoint = '';

    /**
     * @var string
     */
    protected $urlTest = 'https://dev.aymakan.com.sa/api/v2';

    /**
     * @var string
     */
    protected $urlLive = 'https://aymakan.com.sa/api/v2';

    /**
     * Initializes the method.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct($instance_id = 0);

        if ('no' !== $this->enabled) {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'aymakan_render_html'), 10, 1);
            add_action('woocommerce_process_shop_order_meta', 'Aymakan_Shipping_Form::save', 10);
            add_action('wp_ajax_aymakan_shipping_create', array($this, 'aymakan_shipping_create'), 10);
            add_action('wp_ajax_nopriv_aymakan_shipping_create', array($this, 'aymakan_shipping_create'), 10);
        }

        if ('no' == $this->test_mode) {
            $this->endPoint = $this->urlLive;
        } else {
            $this->endPoint = $this->urlTest;
        }

    }

    /**
     * Enqueue JS and CSS Files.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        if (in_array(str_replace('edit-', '', $screen_id), wc_get_order_types('order-meta-boxes'))) {
            wp_enqueue_style('aymakan-shipping', plugins_url('assets/css/aymakan.css', plugin_dir_path(__FILE__)), array(), Aymakan_Main::VERSION, 'all');
            wp_enqueue_script('aymakan-shipping', plugins_url('assets/js/aymakan.js', plugin_dir_path(__FILE__)), array('jquery', 'wp-util'), Aymakan_Main::VERSION, true);
            wp_localize_script(
                'aymakan-shipping',
                'aymakan_shipping',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
        }

    }

    /**
     * Shipping form HTML
     *
     * @return string
     */
    public function aymakan_render_html()
    {
        Aymakan_Shipping_Form::output();
    }


    /**
     * Create Shipping with Aymakan
     *
     * @return false|string
     */
    public function aymakan_shipping_create()
    {

        if (!isset($_POST['data'])) {
            echo json_encode(array('error' => true));
            die();
        }

        $current_user = wp_get_current_user();
        $user_name    = $current_user->first_name . ' ' . $current_user->last_name;

        try {
            $param = array();
            parse_str($_POST['data'], $param);

            $order      = isset($param['order_id']) ? wc_get_order($param['order_id']) : '';
            $name       = isset($param['delivery_name']) ? $param['delivery_name'] : '';
            $email      = isset($param['delivery_email']) ? $param['delivery_email'] : '';
            $phone      = isset($param['delivery_phone']) ? $param['delivery_phone'] : '';
            $address    = isset($param['delivery_address']) ? $param['delivery_address'] : '';
            $city       = isset($param['delivery_city']) ? $param['delivery_city'] : '';
            $region     = isset($param['delivery_region']) ? $param['delivery_region'] : '';
            $pieces     = isset($param['pieces']) ? $param['pieces'] : '';
            $is_cod     = isset($param['is_cod']) ? $param['is_cod'] : '';
            $cod_amount = isset($param['cod_amount']) ? $param['cod_amount'] : '';
            $total      = isset($param['declared_value']) ? $param['declared_value'] : '';
            $reference  = isset($param['reference']) ? $param['reference'] : '';

            $data = array(
                'delivery_name' => $name,
                'delivery_email' => $email,
                'delivery_city' => $city,
                'delivery_address' => $address,
                'delivery_region' => $region,
                'delivery_phone' => $phone,
                'pieces' => $pieces,
                'declared_value' => $total,
                'requested_by' => $user_name,
                'submission_date' => date('Y-m-d H:i:s'),
                'pickup_date' => date('Y-m-d H:i:s'),
                'delivery_date' => date('Y-m-d H:i:s'),
                'reference' => $reference,
                'price_set_currency' => $order->get_currency(),
                'declared_value_currency' => $order->get_currency(),
                'is_cod' => $is_cod,
                'cod_amount' => $cod_amount,
                // 'price_set_amount' => '',
                // 'tax_amount' => '',
                // 'price_set_amount_incl_tax' => '',
                'collection_name' => $this->name,
                'collection_email' => $this->email,
                'collection_city' => $this->city,
                'collection_address' => $this->address,
                'collection_region' => $this->region,
                'collection_phone' => $this->phone,
                'collection_country' => $this->country,
            );

            if ('yes' == $this->debug) {
                $this->log->add($this->id, 'Requesting the Aymakan API...');
                $this->log->add($this->id, print_r($data, true));
            }

            $url = $this->endPoint . '/shipping/create';
            if ('yes' == $this->debug) {
                $this->log->add($this->id, 'URL: ' . $url);
            }

            $response = json_decode($this->api_request($url, $data));

            // Add Order Note.
            if (isset($param['order_id']) && !empty($response->data)) {
                $shipping = $response->data->shipping;
                $tracking = $shipping->tracking_number;
                $date = new DateTime($shipping->created_at);
                $shipping->created_at = $date->format('M d, Y \a\t h:m a');
                $trackpdf = !empty($shipping->label) ? '<a href="' . $shipping->label . '" target="_blank">View PDF</a>' : '';
                $note     = __("<strong>Aymakan Shipment Created</strong>\nTracking Number: {$tracking} \nShipment: {$trackpdf} \nCreated By: {$user_name}", 'woo-aymakan-shipping');
                $order->add_order_note($note);
            }

            echo json_encode($response);
            die();

        } catch (Exception $e) {
            if ('yes' == $this->debug) {
                $this->log->add($this->id, var_dump($e->getMessage()));
            }
        }
    }

    /**
     * @param $url
     * @param $params
     * @return string
     */
    public function api_request($url, $params)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Authorization: " . $this->api_key
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);

        if ('yes' == $this->debug) {
            $this->log->add($this->id, 'Curl response: ' . $response);
        }

        return $response;
    }
}

new Aymakan_Shipping_Create();
