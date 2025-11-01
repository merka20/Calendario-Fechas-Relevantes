<?php
/**
 * Plugin Name: Relevant Dates Calendar
 * Plugin URI: https://merka20.com
 * Description: Customizable calendar with relevant dates and informative tooltips
 * Version: 1.0.0
 * Author: Merka 2.0
 * Author URI: https://merka20.com
 * License: GPL v2 or later
 * Text Domain: calendario-fechas-relevantes
 * Domain Path: /languages
 */


if (!defined('ABSPATH')) exit;

define('MK20_CFR_VERSION', '1.0.0');
define('MK20_CFR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MK20_CFR_PLUGIN_URL', plugin_dir_url(__FILE__));

class MK20_CalendarioFechasRelevantes {
    
    public function __construct() {
        // Cargar traducciones (necesario para plugins privados/no alojados en WordPress.org)
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_shortcode('calendario_fechas', array($this, 'render_calendar'));
        add_action('wp_ajax_MK20_cfr_save_dates', array($this, 'ajax_save_dates'));
    }
    
    /**
     * Cargar traducciones del plugin
     * Nota: Necesario porque este plugin NO está alojado en WordPress.org
     * phpcs:disable PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'calendario-fechas-relevantes',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    // phpcs:enable PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
    
    public function activate() {
        $default_options = array(
            'year' => current_time('Y'),
            'dates' => array(),
            'default_color' => '#ff6b6b',
            'show_spanish_holidays' => true,
            'header_color_1' => '#667eea',
            'header_color_2' => '#764ba2'
        );
        add_option('MK20_cfr_settings', $default_options);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Relevant Dates Calendar', 'calendario-fechas-relevantes'),
            __('Calendar Dates', 'calendario-fechas-relevantes'),
            'manage_options',
            'calendario-fechas-relevantes',
            array($this, 'render_settings_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    public function register_settings() {
        register_setting('MK20_cfr_settings_group', 'MK20_cfr_settings', array($this, 'sanitize_settings'));
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        $sanitized['year'] = isset($input['year']) ? absint($input['year']) : current_time('Y');
        $sanitized['default_color'] = isset($input['default_color']) ? sanitize_hex_color($input['default_color']) : '#ff6b6b';
        $sanitized['header_color_1'] = isset($input['header_color_1']) ? sanitize_hex_color($input['header_color_1']) : '#667eea';
        $sanitized['header_color_2'] = isset($input['header_color_2']) ? sanitize_hex_color($input['header_color_2']) : '#764ba2';
        
        // Sanitizar array de fechas
        $sanitized['dates'] = isset($input['dates']) ? $this->sanitize_dates_array($input['dates']) : array();
        
        $sanitized['show_spanish_holidays'] = isset($input['show_spanish_holidays']) ? true : false;
        return $sanitized;
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_calendario-fechas-relevantes' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('MK20-cfr-admin-styles', MK20_CFR_PLUGIN_URL . 'admin/admin-styles.css', array(), MK20_CFR_VERSION);
        wp_enqueue_script('MK20-cfr-admin-scripts', MK20_CFR_PLUGIN_URL . 'admin/admin-scripts.js', array('jquery', 'wp-color-picker'), MK20_CFR_VERSION, true);
        
        wp_localize_script('MK20-cfr-admin-scripts', 'MK20_cfrAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('MK20_cfr_admin_nonce'),
            'confirmDelete' => __('Are you sure you want to delete this date?', 'calendario-fechas-relevantes')
        ));
    }
    
    public function enqueue_public_scripts() {
        wp_enqueue_style('MK20-cfr-calendar-styles', MK20_CFR_PLUGIN_URL . 'public/calendar-styles.css', array(), MK20_CFR_VERSION);
        wp_enqueue_script('MK20-cfr-calendar-scripts', MK20_CFR_PLUGIN_URL . 'public/calendar-scripts.js', array('jquery'), MK20_CFR_VERSION, true);
        
        // Pasar colores al frontend mediante CSS inline
        $settings = get_option('MK20_cfr_settings');
        $header_color_1 = isset($settings['header_color_1']) ? $settings['header_color_1'] : '#667eea';
        $header_color_2 = isset($settings['header_color_2']) ? $settings['header_color_2'] : '#764ba2';
        
        $custom_css = "
            .MK20-cfr-month-header {
                background: linear-gradient(135deg, {$header_color_1} 0%, {$header_color_2} 100%) !important;
            }
        ";
        wp_add_inline_style('MK20-cfr-calendar-styles', $custom_css);
    }
    
    public function render_settings_page() {
        include MK20_CFR_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    public function render_calendar($atts) {
        $atts = shortcode_atts(array('year' => null), $atts);
        ob_start();
        include MK20_CFR_PLUGIN_DIR . 'public/calendar-display.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX para guardar fechas
     */
    public function ajax_save_dates() {
        check_ajax_referer('MK20_cfr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'calendario-fechas-relevantes'));
        }
        
        // Obtener y sanitizar datos
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitizado en sanitize_dates_array()
        $raw_dates = isset($_POST['dates']) ? wp_unslash($_POST['dates']) : array();
        $dates = $this->sanitize_dates_array($raw_dates);
        
        $settings = get_option('MK20_cfr_settings');
        $settings['dates'] = $dates;
        
        update_option('MK20_cfr_settings', $settings);
        
        wp_send_json_success(__('Dates saved successfully', 'calendario-fechas-relevantes'));
    }
    
    /**
     * Sanitizar array de fechas
     * 
     * @param mixed $dates Array de fechas sin sanitizar
     * @return array Array de fechas sanitizadas
     */
    private function sanitize_dates_array($dates) {
        if (!is_array($dates)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($dates as $date) {
            if (is_array($date)) {
                $sanitized[] = array(
                    'date' => isset($date['date']) ? sanitize_text_field($date['date']) : '',
                    'description' => isset($date['description']) ? sanitize_textarea_field($date['description']) : '',
                    'color' => isset($date['color']) ? sanitize_hex_color($date['color']) : '#ff6b6b'
                );
            }
        }
        return $sanitized;
    }
    
    /**
     * Obtener festivos de España para un año específico
     * 
     * @param int $year Año
     * @return array Array de festivos
     */
    public static function get_spanish_holidays($year) {
        $holidays = array();
        
        $fixed_holidays = array(
            '01-01' => __('New Year', 'calendario-fechas-relevantes'),
            '01-06' => __('Epiphany', 'calendario-fechas-relevantes'),
            '05-01' => __('Labour Day', 'calendario-fechas-relevantes'),
            '08-15' => __('Assumption of Mary', 'calendario-fechas-relevantes'),
            '10-12' => __('National Day of Spain', 'calendario-fechas-relevantes'),
            '11-01' => __('All Saints Day', 'calendario-fechas-relevantes'),
            '12-06' => __('Spanish Constitution Day', 'calendario-fechas-relevantes'),
            '12-08' => __('Immaculate Conception', 'calendario-fechas-relevantes'),
            '12-25' => __('Christmas', 'calendario-fechas-relevantes')
        );
        
        foreach ($fixed_holidays as $date => $name) {
            $holidays[$year . '-' . $date] = array(
                'date' => $year . '-' . $date,
                'title' => $name,
                'description' => __('Spanish national holiday', 'calendario-fechas-relevantes'),
                'color' => '#dc3545',
                'is_holiday' => true
            );
        }
        
        // Semana Santa
        $easter = self::calculate_easter($year);
        
        $jueves_santo_timestamp = strtotime($easter . ' -3 days');
        $jueves_santo = gmdate('Y-m-d', $jueves_santo_timestamp);
        
        $holidays[$jueves_santo] = array(
            'date' => $jueves_santo,
            'title' => __('Maundy Thursday', 'calendario-fechas-relevantes'),
            'description' => __('Spanish national holiday - Holy Week', 'calendario-fechas-relevantes'),
            'color' => '#dc3545',
            'is_holiday' => true
        );
        
        $viernes_santo_timestamp = strtotime($easter . ' -2 days');
        $viernes_santo = gmdate('Y-m-d', $viernes_santo_timestamp);
        
        $holidays[$viernes_santo] = array(
            'date' => $viernes_santo,
            'title' => __('Good Friday', 'calendario-fechas-relevantes'),
            'description' => __('Spanish national holiday - Holy Week', 'calendario-fechas-relevantes'),
            'color' => '#dc3545',
            'is_holiday' => true
        );
        
        return $holidays;
    }
    
    /**
     * Calcular la fecha de Pascua
     * Algoritmo de Meeus/Jones/Butcher
     * 
     * @param int $year Año
     * @return string Fecha en formato Y-m-d
     */
    private static function calculate_easter($year) {
        $a = $year % 19;
        $b = intval($year / 100);
        $c = $year % 100;
        $d = intval($b / 4);
        $e = $b % 4;
        $f = intval(($b + 8) / 25);
        $g = intval(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intval($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intval(($a + 11 * $h + 22 * $l) / 451);
        $month = intval(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}

new MK20_CalendarioFechasRelevantes();