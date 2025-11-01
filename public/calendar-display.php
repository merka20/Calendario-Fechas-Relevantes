<?php
if (!defined('ABSPATH')) exit;

$settings = get_option('MK20_cfr_settings');
$year = !empty($atts['year']) ? intval($atts['year']) : $settings['year'];
$dates = isset($settings['dates']) ? $settings['dates'] : array();
$show_spanish_holidays = isset($settings['show_spanish_holidays']) ? $settings['show_spanish_holidays'] : true;

$dates_by_date = array();
$custom_dates = array();

foreach ($dates as $date) {
    if (isset($date['date'])) {
        $dates_by_date[$date['date']] = $date;
        $custom_dates[$date['date']] = true;
    }
}

if ($show_spanish_holidays) {
    $spanish_holidays = MK20_CalendarioFechasRelevantes::get_spanish_holidays($year);
    foreach ($spanish_holidays as $date => $holiday) {
        if (!isset($dates_by_date[$date])) {
            $dates_by_date[$date] = $holiday;
        }
    }
}

$months = array(
    1 => __('January', 'calendario-fechas-relevantes'),
    2 => __('February', 'calendario-fechas-relevantes'),
    3 => __('March', 'calendario-fechas-relevantes'),
    4 => __('April', 'calendario-fechas-relevantes'),
    5 => __('May', 'calendario-fechas-relevantes'),
    6 => __('June', 'calendario-fechas-relevantes'),
    7 => __('July', 'calendario-fechas-relevantes'),
    8 => __('August', 'calendario-fechas-relevantes'),
    9 => __('September', 'calendario-fechas-relevantes'),
    10 => __('October', 'calendario-fechas-relevantes'),
    11 => __('November', 'calendario-fechas-relevantes'),
    12 => __('December', 'calendario-fechas-relevantes')
);

$days_of_week = array(
    __('M', 'calendario-fechas-relevantes'),
    __('T', 'calendario-fechas-relevantes'),
    __('W', 'calendario-fechas-relevantes'),
    __('T', 'calendario-fechas-relevantes'),
    __('F', 'calendario-fechas-relevantes'),
    __('S', 'calendario-fechas-relevantes'),
    __('S', 'calendario-fechas-relevantes')
);

/**
 * Función para formatear fecha en texto
 * 
 * @param string $date_string Fecha en formato Y-m-d
 * @param array  $months_array Array de nombres de meses
 * @return string Fecha formateada
 */
function MK20_cfr_format_date($date_string, $months_array) {
    $date_parts = explode('-', $date_string);
    if (count($date_parts) === 3) {
        $day = intval($date_parts[2]);
        $month_num = intval($date_parts[1]);
        $month_name = isset($months_array[$month_num]) ? $months_array[$month_num] : '';
        
        return sprintf(
            /* translators: %1$d: day number, %2$s: month name */
            _x('%1$d of %2$s', 'date format: day of month', 'calendario-fechas-relevantes'),
            $day,
            $month_name
        );
    }
    return $date_string;
}
?>

<div class="MK20-cfr-calendar-container">
    <h2 class="MK20-cfr-calendar-year">
        <?php 
        /* translators: %d: year number */
        printf(esc_html__('📅 Calendar %d', 'calendario-fechas-relevantes'), absint($year)); 
        ?>
    </h2>
    
    <div class="MK20-cfr-calendar-grid">
        <?php
        foreach ($months as $month_num => $month_name) {
            $first_day = mktime(0, 0, 0, $month_num, 1, $year);
            $days_in_month = gmdate('t', $first_day);
            $day_of_week = gmdate('N', $first_day);
            ?>
            
            <div class="MK20-cfr-month">
                <div class="MK20-cfr-month-header">
                    <h3><?php echo esc_html($month_name); ?></h3>
                </div>
                
                <div class="MK20-cfr-days-header">
                    <?php foreach ($days_of_week as $day) : ?>
                        <div class="MK20-cfr-day-name"><?php echo esc_html($day); ?></div>
                    <?php endforeach; ?>
                </div>
                
                <div class="MK20-cfr-days-grid">
                    <?php
                    for ($i = 1; $i < $day_of_week; $i++) {
                        echo '<div class="MK20-cfr-day MK20-cfr-day-empty"></div>';
                    }
                    
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $current_date = sprintf('%04d-%02d-%02d', $year, $month_num, $day);
                        $current_timestamp = strtotime($current_date);
                        $current_day_of_week = gmdate('N', $current_timestamp);
                        
                        $is_weekend = ($current_day_of_week == 6 || $current_day_of_week == 7);
                        $has_event = isset($dates_by_date[$current_date]);
                        $is_custom_date = isset($custom_dates[$current_date]);
                        
                        $class = 'MK20-cfr-day';
                        
                        // FECHA PERSONALIZADA
                        if ($is_custom_date) {
                            $class .= ' MK20-cfr-day-event';
                            $event = $dates_by_date[$current_date];
                            $bg_color = isset($event['color']) ? $event['color'] : '#ff6b6b';
                            
                            $formatted_date = MK20_cfr_format_date($current_date, $months);
                            $description = isset($event['description']) ? $event['description'] : '';
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" 
                                 style="background-color: <?php echo esc_attr($bg_color); ?>;"
                                 data-title="<?php echo esc_attr($formatted_date); ?>"
                                 data-description="<?php echo esc_attr($description); ?>">
                                <span class="MK20-cfr-day-number"><?php echo absint($day); ?></span>
                            </div>
                            <?php
                        }
                        // FESTIVO
                        elseif ($has_event) {
                            $class .= ' MK20-cfr-day-event MK20-cfr-day-holiday';
                            $event = $dates_by_date[$current_date];
                            $bg_color = '#dc3545';
                            
                            if ($is_weekend) {
                                $class .= ' MK20-cfr-day-weekend';
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" 
                                 style="background-color: <?php echo esc_attr($bg_color); ?>;"
                                 data-title="<?php echo esc_attr($event['title']); ?>"
                                 data-description="<?php echo esc_attr($event['description']); ?>">
                                <span class="MK20-cfr-day-number"><?php echo absint($day); ?></span>
                            </div>
                            <?php
                        }
                        // FIN DE SEMANA
                        elseif ($is_weekend) {
                            $class .= ' MK20-cfr-day-weekend';
                            $day_name = $current_day_of_week == 6 
                                ? __('Saturday', 'calendario-fechas-relevantes')
                                : __('Sunday', 'calendario-fechas-relevantes');
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" 
                                 data-title="<?php echo esc_attr($day_name); ?>"
                                 data-description="<?php esc_attr_e('Weekend', 'calendario-fechas-relevantes'); ?>">
                                <span class="MK20-cfr-day-number"><?php echo absint($day); ?></span>
                            </div>
                            <?php
                        }
                        // DÍA NORMAL
                        else {
                            ?>
                            <div class="<?php echo esc_attr($class); ?>">
                                <span class="MK20-cfr-day-number"><?php echo absint($day); ?></span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<div id="MK20-cfr-tooltip" class="MK20-cfr-tooltip MK20-cfr-tooltip-hidden">
    <div class="MK20-cfr-tooltip-title"></div>
    <div class="MK20-cfr-tooltip-description"></div>
</div>