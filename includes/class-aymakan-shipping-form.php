<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Aymakan_Shipping_Form
 */
class Aymakan_Shipping_Form
{
    /**
     * Output the metabox.
     */
    public static function output()
    {
        global $post, $thepostid, $theorder;

        if (!is_int($thepostid)) {
            $thepostid = $post->ID;
        }

        if (!is_object($theorder)) {
            $theorder = wc_get_order($thepostid);
        }

        $order  = $theorder;
        $fields = new Aymakan_Shipping_Form();

        include 'views/shipping-form.php';
    }

    /**
     * @param $order
     * @param $column
     * @return string
     */
    public function shipping_form($order, $column)
    {
        $cod_fee = '';

        // Get the customer shipping and billing email
        $email      = $order->get_billing_email();
        $phone      = $order->get_billing_phone();
        $first_name = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : $order->get_billing_first_name();
        $last_name  = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : $order->get_billing_last_name();
        $company    = $order->get_shipping_company() ? $order->get_shipping_company() : $order->get_billing_company();
        $address_2  = $order->get_shipping_address_2() ? '|| ' . $order->get_shipping_address_2() : $order->get_billing_address_2();
        $address_1  = $order->get_shipping_address_1() ? $order->get_shipping_address_1() . $address_2 : $order->get_billing_address_1() . $address_2;
        $city       = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        $state      = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
        $postcode   = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode();
        $country    = $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country();

        // Get COD Fee
        foreach ($order->get_items('fee') as $item_id => $item_fee) {
            if ($item_fee->get_name() !== 'COD fee')
                continue;
            $cod_fee .= $item_fee->get_total();
        }

        $fields = array();
        switch ($column):
            case ('column_1'):
                $fields = array(
                    'delivery_name' => array(
                        'title' => __('Name', 'woo-aymakan-shipping'),
                        'label' => __('Name', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Customer Name.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $first_name . '' . $last_name,
                        'required' => true
                    ),
                    'delivery_email' => array(
                        'title' => __('Email', 'woo-aymakan-shipping'),
                        'label' => __('Email', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Customer Email.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $email,
                        'required' => true
                    ),
                    'delivery_phone' => array(
                        'title' => __('Phone', 'woo-aymakan-shipping'),
                        'label' => __('Phone', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Phone Number.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $phone,
                        'required' => true
                    ),
                    'delivery_address' => array(
                        'title' => __('Address', 'woo-aymakan-shipping'),
                        'label' => __('Address', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Address.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $address_1,
                        'required' => true
                    ),
                    'delivery_city' => array(
                        'title' => __('City', 'woo-aymakan-shipping'),
                        'label' => __('City', 'woo-aymakan-shipping'),
                        'type' => 'select',
                        'options' => Aymakan_Shipping_Helper::get_cities(),
                        'description' => __('Select City.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $city
                    ),
                    'delivery_neighbourhood' => array(
                        'title' => __('Neighbourhood', 'woo-aymakan-shipping'),
                        'label' => __('Neighbourhood', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Neighbourhood.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $state
                    ),
                    /*'delivery_postcode' => array(
                        'title' => __('Postcode', 'woo-aymakan-shipping'),
                        'label' => __('Postcode', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Enter Address Postcode.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $postcode
                    ),*/
                );
                break;
            case ('column_2'):
                $fields = array(
                    'reference' => array(
                        'title' => __('Reference', 'woo-aymakan-shipping'),
                        'label' => __('Reference', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Reference must be order number.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $order->get_id(),
                        'required' => true
                    ),
                    'declared_value' => array(
                        'title' => __('Order Total', 'woo-aymakan-shipping'),
                        'label' => __('Order Total', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Order grand total.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $order->get_total(),
                        'required' => true
                    ),
                    'is_cod' => array(
                        'title' => __('Is COD', 'woo-aymakan-shipping'),
                        'label' => __('Is COD', 'woo-aymakan-shipping'),
                        'type' => 'select',
                        'options' => array(
                            'No',
                            'Yes'
                        ),
                        'description' => __('If order is COD, then select Yes.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => ($order->get_payment_method() == 'cod') ? 1 : 0
                    ),
                    'cod_amount' => array(
                        'title' => __('COD Amount', 'woo-aymakan-shipping'),
                        'label' => __('COD Amount', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('If order is COD, then COD amount is the amount Aymakan driver will be collecting from your customer.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => $cod_fee,
                        'required' => true
                    ),
                    'items' => array(
                        'title' => __('Items', 'woo-aymakan-shipping'),
                        'label' => __('Items', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Number of items in the shipment.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => count($order->get_items()),
                        'required' => true
                    ),
                    'pieces' => array(
                        'title' => __('Pieces', 'woo-aymakan-shipping'),
                        'label' => __('Pieces', 'woo-aymakan-shipping'),
                        'type' => 'text',
                        'description' => __('Pieces in the shipment. For example, for a large orders, the items can be boxed in multiple cartons. The number of boxed cartons should be entered here.', 'woo-aymakan-shipping'),
                        'class' => array('desc_tip'),
                        'default' => __('', 'woo-aymakan-shipping'),
                        'required' => true
                    ),
                    'order_id' => array(
                        'type' => 'hidden',
                        'default' => $order->get_id(),
                    )
                );
                break;
        endswitch;

        $html = '';
        foreach ($fields as $key => $field) {
            $field['return'] = true;
            $value           = isset($field['default']) ? $field['default'] : '';
            $html            .= woocommerce_form_field($key, $field, $value);
        }
        return $html;
    }
}

?>
