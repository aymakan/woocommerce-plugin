<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Aymakan_Shipping_Helper
 */
class Aymakan_Shipping_Helper
{
    /**
     * @var string
     */
    public static $enabled = 'yes';

    /**
     * @var string
     */
    public static $api_key = '';

    /**
     * @var string
     */
    public static $test_mode = 'yes';

    /**
     * @var string
     */
    public static $debug = 'yes';

    /**
     * @var string
     */
    public static $endPoint = '';

    /**
     * @var string
     */
    protected $urlTest = 'https://dev-api.aymakan.com.sa/v2';

    /**
     * @var string
     */
    protected $urlLive = 'https://api.aymakan.net/v2';

    public function __construct()
    {
        add_filter('woocommerce_form_field', array($this, 'aymakan_form_extend'), 10, 4);
        $this->init();
    }

    public function init()
    {
        // Define user set variables.
        self::$enabled   = $this->get_option('enabled');
        self::$api_key   = $this->get_option('api_key');
        self::$test_mode = $this->get_option('test_mode');
        self::$debug     = $this->get_option('debug');

        if ('no' === self::$test_mode) {
            self::$endPoint = $this->urlLive;
        } else {
            self::$endPoint = $this->urlTest;
        }

    }

    public function get_option($key)
    {
        $option = get_option('woocommerce_aymakan_settings');
        return (isset($option[$key]) ? $option[$key] : '');
    }

    public static function api_request($segment, $params = array())
    {
        $url  = self::$endPoint . $segment;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Authorization: " . self::$api_key
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);
        if ('yes' === self::$debug) {
            self::add_log('Curl response: ' . $response);
        }
        return $response;
    }

    public static function get_cities($defaultCity = null)
    {
        $response = json_decode(self::api_request('/cities'), true);

        if ($defaultCity) {
            $cities = [$defaultCity => $defaultCity];
        } else {
            $cities = ['' => __('Select City', 'aymakan')];
        }

        if (!empty($response['data']) && !empty($response['data']['cities'])) {
            foreach ($response['data']['cities'] as $city) {
                if (get_locale() == 'ar_SA') {
                    $cities[$city['city_en']] = $city['city_ar'];
                } else {
                    $cities[$city['city_en']] = $city['city_en'];
                }
            }
        }
        return $cities;
    }

    public static function add_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    /**
     * @param $field
     * @param $key
     * @param $args
     * @param $value
     * @return string
     */
    public function aymakan_form_extend($field, $key, $args, $value)
    {
        if ($args['type'] == 'hidden') {
            $field .= '<input type="' . esc_attr($args['type']) . '" name="' . esc_attr($key) . '"  value="' . esc_attr($value) . '" />';
        }
        return $field;
    }

    public static function dd($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit;
    }
}

new Aymakan_Shipping_Helper();
