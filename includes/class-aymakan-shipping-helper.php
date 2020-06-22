<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Aymakan_Shipping_Helper
 */
class Aymakan_Shipping_Helper
{
    public function __construct()
    {
        add_filter('woocommerce_form_field', array($this, 'aymakan_form_extend'), 10, 4);
    }

    /**
     * @param string $locale
     * @return array|false
     */
    public static function get_cities($locale = 'ar')
    {
        $cities = array(
            'Riyadh' => 'الرياض',
            'Khobar' => 'الخبر',
            'Dammam' => 'الدمام',
            'Afif' => 'عفيف',
            'Abha' => 'أبها',
            'Abqaiq' => 'بقيق',
            'Abu Areish' => 'أبو عريش',
            'Aflaj' => 'الأفلاج',
            'Ahad Masarha' => 'أحد المسارحة',
            'Ahad Rufaidah' => 'أحد رفيدة',
            'Ain Dar' => 'عين دار ',
            'Al Dalemya' => 'الدليمية',
            'Al Hassa' => 'الأحساء',
            'Alghat' => 'الغاط',
            'Alhada' => 'الهدا',
            'Al-Jsh' => 'الجش',
            'AlRass' => 'الرس',
            'Amaq ' => 'عمق',
            'Anak' => 'عنك',
            'Aqiq ' => 'عقيق',
            'Arar' => 'عرعر',
            'Artawiah' => 'الأرطاوية',
            'Asfan' => 'عسفان',
            'Ash Shuqaiq' => 'الشقيق',
            'Assiyah' => 'الأسياح',
            'Awamiah' => 'العوامية',
            'Ayn Fuhayd' => 'عين ابن فهيد',
            'Badaya' => 'البدايع',
            'Bader' => 'بدر',
            'Baha ' => 'الباحة',
            'Bahara ' => 'بحرة',
            'Balahmar' => 'بالأحمر',
            'Balasmar ' => 'بالأسمر',
            'Bareq ' => 'بارق',
            'Batha' => 'بطحاء',
            'BilJurashi ' => 'بلجرشي',
            'Birk ' => 'البرك',
            'Bish ' => 'بيش',
            'Bisha ' => 'بيشة',
            'Bukeiriah ' => 'البكيرية',
            'Buraidah' => 'بريدة',
            'Damad' => 'ضمد',
            'Darb' => 'درب',
            'Dawadmi' => 'الدوادمي',
            'Daelim' => 'الدلم',
            'Deraab' => 'ديراب',
            'Dere\'iyeh ' => 'الدرعية',
            'Dhahran ' => 'الظهران',
            'Dhahran Al Janoob' => 'ظهران الجنوب',
            'Dhurma' => 'ضرما',
            'Domat Al Jandal ' => 'دومة الجندل',
            'Duba' => 'ضبا',
            'Farasan' => 'فرسان',
            'Gilwa' => 'قلوة',
            'Gizan' => 'جازان',
            'Hadeethah' => 'الحديثة',
            'Hafer Al Batin' => 'حفر الباطن',
            'Hail' => 'حائل',
            'Halat Ammar' => 'حالة عمار ',
            'Haqil' => 'حقيل',
            'Hareeq ' => 'الحريق',
            'Hawea' => 'الحوية',
            'Hawtat Bani Tamim' => 'حوطة بني تميم',
            'Hinakeya' => 'الحناكية',
            'Hofuf ' => 'الهفوف',
            'Horaimal' => 'حريملاء',
            'Hotat Sudair' => 'حوطة سدير',
            'Khafji' => 'الخفجي',
            'Khaibar' => 'خيبر',
            'Khamaseen' => 'الخماسين',
            'Khamis Mushait' => 'خميس مشيط',
            'Kharj' => 'الخرج',
            'Khodaria' => 'الخضرية',
            'Khulais' => 'خليص',
            'Khurma' => 'الخرمة',
            'Laith' => 'ليث',
            'Madinah' => 'المدينة',
            'Mahad Al Dahab' => 'مهد الذهب',
            'Majarda' => 'المجاردة',
            'Majma' => 'المجمعة',
            'Makkah' => 'مكة المكرمة',
            'Mandak' => 'المندق',
            'Mastura' => 'مستورة',
            'Mikhwa' => 'المخواة',
            'Mohayel Aseer' => 'محايل عسير ',
            'Mrat ' => 'مرات',
            'Mubaraz' => 'المبرز',
            'Mulaija' => 'مليجة',
            'Muzahmiah ' => 'المزاحمية',
            'Nabiya ' => 'النابية',
            'Najran' => 'نجران',
            'Namas' => 'النماص',
            'Nimra' => 'نمرة',
            'Noweirieh' => 'النعيرية',
            'Nwariah ' => 'نوارية',
            'Onaiza ' => 'عنيزة',
            'Othmanyah ' => 'العثمانية',
            'Oula ' => 'علا',
            'Oyaynah' => 'العيينة',
            'Qahmah ' => 'القحمة',
            'Qarah' => 'قرة',
            'Qariya Al Olaya ' => 'قرية العليا',
            'Qasab' => 'القصب',
            'Qassim' => 'القصيم',
            'Qatif' => 'القطيف',
            'Qaysoomah' => 'القيصومة',
            'Qunfudah ' => 'القنفذة',
            'Qurayat' => 'القريات',
            'Quwei\'ieh' => 'القويعية',
            'Rabigh ' => 'رابغ',
            'Rafha' => 'رفحاء',
            'Rahima' => 'رحيمة',
            'Rania' => 'رنية',
            'Ras Al Kheir' => 'رأس الخير',
            'Ras Tanura ' => 'رأس تنورة',
            'Rejal Alma\'a' => 'رجال المع',
            'Remah' => 'رماح',
            'Riyadh Al Khabra' => 'رياض الخبراء',
            'Rowdat Sodair' => 'روضة سدير',
            'Sabt El Alaya' => 'سبت العليا',
            'Sabya ' => 'صبيا',
            'Safanyah' => 'السفانية',
            'Safwa' => 'صفوى',
            'Sajir' => 'ساجر',
            'Sakaka' => 'سكاكا',
            'Salbookh' => 'صلبوخ',
            'Salwa ' => 'سلوى',
            'Samtah ' => 'صامطة',
            'Sarar' => 'الصرار',
            'Sarat Obeida' => 'سرة عبيدة',
            'Seiha' => 'سيهات',
            'Shaqra ' => 'شقراء',
            'Sharourah' => 'شرورة',
            'Shefaa' => 'الشفاء',
            'Shoaiba' => 'الشعيبة',
            'Shraie\'e' => 'الشرايع',
            'Shumeisi' => 'الشميسي',
            'Sulaiyl' => 'السليل',
            'Tabrjal ' => 'طبرجل',
            'Tabuk' => 'تبوك',
            'Taif' => 'الطائف',
            'Tanjeeb' => 'تناجيب',
            'Tanuma' => 'تنومة',
            'Tarut ' => 'تاروت',
            'Tatleeth' => 'تثليث',
            'Tayma' => 'تيماء',
            'Tebrak' => 'تبراك',
            'Thuqba' => 'الثقبة',
            'Turaif' => 'طريف',
            'Turba' => 'تربا',
            'Udhaliyah' => 'العضيلية',
            'Um Aljamajim' => 'ام الجماجم',
            'umluj' => 'أملج',
            'Uqlat Al Suqur' => 'عقلة الصقور',
            'Uyun ' => 'العيون',
            'Wadi El Dwaser' => 'وادي الدواسر',
            'Wadi Fatmah' => 'وادي فاطمة',
            'Al Wajh' => 'الوجة',
            'Yanbu' => 'ينبع',
            'Yanbu Al Baher' => 'ينبع البحر',
            'Zahban' => 'ذهبان',
            'Zulfi ' => 'الزلفي',
            'Bahrat Al Moujoud' => 'بحرة المجود',
            'Kara ' => 'الكرا',
            'Kara\'a' => 'كرا',
            'Khasawyah' => 'الخصاوية',
            'Harjah ' => 'الهرجة',
            'Thabya' => 'صبيا',
            'Satorp' => 'ارامكو توتال للتكرير,الجبيل',
            'Sahna ' => 'الصحنة',
            'Rwaydah ' => 'الرويضة',
            'Muthaleif' => 'المظليف',
            'Midinhab ' => 'المذنب',
            'Jeddah' => 'جدة‎‎'
        );

        if ($locale == 'en') {
            $cities = array_combine(array_flip($cities), array_flip($cities));
        }

        return $cities;
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
}

new Aymakan_Shipping_Helper();
