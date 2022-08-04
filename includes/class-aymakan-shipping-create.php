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
     * @var
     */
    public $message;

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
            //add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'aymakan_render_html'), 10, 1);
            add_action('woocommerce_process_shop_order_meta', 'Aymakan_Shipping_Form::save', 10);
            add_action('wp_ajax_aymakan_shipping_create', array($this, 'aymakan_shipping_create'), 10);
            add_action('wp_ajax_nopriv_aymakan_shipping_create', array($this, 'aymakan_shipping_create'), 10);
            add_action('wp_ajax_aymakan_bulk_shipping_create', array($this, 'aymakan_bulk_shipping_create'), 10);
            add_action('wp_ajax_nopriv_aymakan_bulk_shipping_create', array($this, 'aymakan_bulk_shipping_create'), 10);
            add_filter('manage_edit-shop_order_columns', array($this, 'aymakan_wc_new_order_column'));
            add_action('manage_shop_order_posts_custom_column', array($this, 'aymakan_add_aymakan_action_column'));
            add_action('manage_shop_order_posts_custom_column', array($this, 'aymakan_add_aymakan_action_column'));
            add_filter('bulk_actions-edit-shop_order', array($this, 'aymakan_bulk_shipment_actions'), 99, 2);
            add_filter('handle_bulk_actions-edit-shop_order', array($this, 'aymakan_handle_bulk_shipment_actions'), 10, 3);
            add_action('admin_menu', array($this, 'aymakan_shipping_create_page'));
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

        wp_enqueue_style('aymakan-shipping-global', plugins_url('assets/css/global.css', plugin_dir_path(__FILE__)), array(), Aymakan_Main::VERSION, 'all');
        if (in_array($screen_id, ['woo-aymakan-shipping-carrier/create-shipping-page', 'edit-shop_order'], true)) {
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
     * Create Shipping with Aymakan
     *
     * @throws JsonException
     */
    public function aymakan_shipping_create()
    {

        if (!isset($_POST['data'])) {
            echo json_encode(array('error' => true));
            die();
        }

        $param = array();
        parse_str($_POST['data'], $param);

        if (get_post_meta($param['order_id'], 'aymakan_awb_link', true)) {
            echo json_encode([
                'error' => true,
                'message' => 'Shipment Already Created'
            ], JSON_THROW_ON_ERROR);
            die();
        }

        try {

            $order         = isset($param['order_id']) ? wc_get_order($param['order_id']) : '';
            $name          = isset($param['delivery_name']) ? $param['delivery_name'] : '';
            $email         = isset($param['delivery_email']) ? $param['delivery_email'] : '';
            $phone         = isset($param['delivery_phone']) ? $param['delivery_phone'] : '';
            $address       = isset($param['delivery_address']) ? $param['delivery_address'] : '';
            $city          = isset($param['delivery_city']) ? $param['delivery_city'] : '';
            $neighbourhood = isset($param['delivery_neighbourhood']) ? $param['delivery_neighbourhood'] : '';
            $pieces        = isset($param['pieces']) ? $param['pieces'] : 1;
            $is_cod        = isset($param['is_cod']) ? $param['is_cod'] : '';
            $cod_amount    = isset($param['cod_amount']) ? $param['cod_amount'] : '';
            $total         = isset($param['declared_value']) ? $param['declared_value'] : '';
            $reference     = isset($param['reference']) ? $param['reference'] : $param['order_id'];

            $data = [
                'delivery_name' => $name,
                'delivery_email' => $email,
                'delivery_city' => $city,
                'delivery_address' => $address,
                'delivery_phone' => $phone,
                'delivery_neighbourhood' => $neighbourhood,
                'pieces' => $pieces,
                'declared_value' => $total,
                'price_set_currency' => $order->get_currency(),
                'declared_value_currency' => $order->get_currency(),
                'is_cod' => $is_cod,
                'cod_amount' => $cod_amount,
                'reference' => $reference,
            ];

            $response = $this->aymakan_response_format($order, $data);

            echo json_encode($response, JSON_THROW_ON_ERROR);
            die();

        } catch (JsonException $e) {
            if ('yes' === $this->debug) {
                $this->log->add($this->id, var_dump($e->getMessage()));
            }
        }
    }

    /**
     * Create Bulk Shipping with Aymakan
     *
     * @throws JsonException
     */
    public function aymakan_bulk_shipping_create()
    {
        $param = array();
        parse_str($_POST['data'], $param);

        if (!isset($param['post'])) {
            echo json_encode([[
                'error' => true,
                'id' => 0,
                'message' => __('Please select an order.', 'aymakan'),
            ]], JSON_THROW_ON_ERROR);
            die();
        }

        $letEncode = [];
        try {
            foreach ($param['post'] as $id) {

                if (get_post_meta($id, 'aymakan_awb_link', true)) {
                    $letEncode[] = [
                        'id' => $id,
                        'error' => true,
                        'message' => __('Shipment Already Created.', 'aymakan'),
                    ];
                    continue;
                }

                $order = wc_get_order($id);

                $first_name = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : $order->get_billing_first_name();
                $last_name  = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : $order->get_billing_last_name();
                $email      = $order->get_billing_email();
                $address_2  = $order->get_shipping_address_2() ? ' Address 2. ' . $order->get_shipping_address_2() : ' Address 2. ' . $order->get_billing_address_2();
                $address    = $order->get_shipping_address_1() ? $order->get_shipping_address_1() . $address_2 : $order->get_billing_address_1() . $address_2;
                $phone      = $order->get_billing_phone();
                $city       = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();

                /*
                $country    = $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country();
                $state      = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
                */

                $data = [
                    'delivery_name' => $first_name . ' ' . $last_name,
                    'delivery_email' => $email,
                    'delivery_city' => $city,
                    'delivery_address' => $address,
                    'delivery_phone' => $phone,
                    'delivery_neighbourhood' => $this->neighbourhood,
                    'pieces' => 1,
                    'declared_value' => $order->get_total(),
                    'price_set_currency' => $order->get_currency(),
                    'declared_value_currency' => $order->get_currency(),
                    'is_cod' => ($order->get_payment_method() === 'cod') ? 1 : 0,
                    'cod_amount' => ($order->get_payment_method() === 'cod') ? $order->get_total() : '',
                    'reference' => $id,
                ];

                $letEncode[] = $this->aymakan_response_format($order, $data);

            }

            echo json_encode($letEncode, JSON_THROW_ON_ERROR);
            die();

        } catch (JsonException $e) {
            if ('yes' === $this->debug) {
                $this->log->add($this->id, var_dump($e->getMessage()));
            }
        }

    }

    /**
     * @throws JsonException
     */
    public function aymakan_response_format($order, $data)
    {
        if ('yes' === $this->debug) {
            $this->log->add($this->id, 'Requesting the Aymakan API...');
            $this->log->add($this->id, print_r($data, true));
        }

        $currentUser  = wp_get_current_user();
        $userFullName = $currentUser->first_name . ' ' . $currentUser->last_name;

        $data = array_merge($data, [
            'delivery_country' => 'Saudi Arabia',
            'collection_name' => $this->name,
            'collection_email' => $this->email,
            'collection_city' => $this->city,
            'collection_address' => $this->address,
            'collection_neighbourhood' => $this->neighbourhood,
            'collection_phone' => $this->phone,
            'collection_country' => 'Saudi Arabia',
            'requested_by' => $userFullName,
            'submission_date' => date('Y-m-d H:i:s'),
            'pickup_date' => date('Y-m-d H:i:s'),
            //'delivery_date' => date('Y-m-d H:i:s'),
            'channel' => 'woocommerce',
        ]);

        $response = json_decode(Aymakan_Shipping_Helper::api_request('/shipping/create', $data), false, 512, JSON_THROW_ON_ERROR);

        if (!empty($response->shipping)) {
            $shipping             = $response->shipping;
            $tracking             = $shipping->tracking_number;
            $date                 = new DateTime($shipping->created_at);
            $shipping->created_at = $date->format('M d, Y \a\t h:m a');
            $trackpdf             = !empty($shipping->pdf_label) ? '<a href="' . $shipping->pdf_label . '" target="_blank">View PDF</a>' : '';
            $order->add_order_note(__("<strong>Aymakan Shipment Created</strong>\nTracking Number: {$tracking} \nShipment: {$trackpdf} \nCreated By: {$userFullName}", 'aymakan'));
            $response->id = $order->id;

            $response->tracking_link = 'https://aymakan.com.sa/en/tracking/' . $tracking;
            if ('yes' === $this->test_mode) {
                $response->tracking_link = 'https://dev.aymakan.com.sa/en/tracking/' . $tracking;
            }

            update_post_meta($order->id, 'aymakan_awb_link', $shipping->pdf_label);
            update_post_meta($order->id, 'aymakan_tracking_link', $response->tracking_link);
            update_post_meta($order->id, 'aymakan_tracking_number', $tracking);
        }

        return $response;
    }

    /**
     * @param $actions
     * @return mixed
     */
    public function aymakan_bulk_shipment_actions($actions)
    {
        $actions['aymakan_bulk_shipment'] = __('Create Aymakan Shipments', 'aymakan');
        return $actions;
    }

    /**
     * @param $redirect_to
     * @param $action
     * @param $ids
     * @return string
     */
    public function aymakan_handle_bulk_shipment_actions($redirect_to, $action, $ids)
    {

        if ($action == 'aymakan_bulk_shipment') {
            // return admin_url() . 'admin.php?page=woo-aymakan-shipping-carrier/create-shipping-page.php&order_ids=' . implode('|', $ids);
            return '';
        }

        return esc_url_raw($redirect_to);
    }

    /**
     * @return void
     */
    public function aymakan_shipping_create_page()
    {
        add_menu_page('Create Shipment', 'Aymakan Create shipment', 'manage_options', AYMAKAN_PATH . 'create-shipping-page.php', '', 'dashicons-welcome-widgets-menus', 90);
    }

    /**
     * @param $columns
     * @return mixed
     */
    public function aymakan_wc_new_order_column($columns)
    {
        $columns['aymakan']          = 'Aymakan Action';
        $columns['aymakan-tracking'] = 'Aymakan Tracking';
        return $columns;
    }

    /**
     * @param $column
     * @return void
     */
    public function aymakan_add_aymakan_action_column($column)
    {
        global $post;

        $AwbLink        = get_post_meta($post->ID, 'aymakan_awb_link', true);
        $TrackingLink   = get_post_meta($post->ID, 'aymakan_tracking_link', true);
        $TrackingNumber = get_post_meta($post->ID, 'aymakan_tracking_number', true);

        if ('aymakan' === $column) {
            if (!$AwbLink) {
                echo '<a href="' . admin_url() . 'admin.php?page=woo-aymakan-shipping-carrier/create-shipping-page.php&order_ids=' . $post->ID . '" class="order-status aymakan-btn aymakan-shipping-create-btn">' . __('Create Shipment', 'aymakan') . '</a>';
            } else {
                echo '<a href="' . $AwbLink . '" class="order-status aymakan-btn aymakan-awb-btn" target="_blank">' . __('Print Airway Bill', 'aymakan') . '</a>';
            }
        }

        if (('aymakan-tracking' === $column) && $AwbLink && $TrackingNumber) {

            echo '<a href="' . $TrackingLink . '" class="order-status aymakan-btn aymakan-shipping-track-btn" target="_blank">' . $TrackingNumber . '</a>';
        }

    }


}

new Aymakan_Shipping_Create();
