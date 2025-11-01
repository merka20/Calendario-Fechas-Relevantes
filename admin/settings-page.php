<?php
if (!defined('ABSPATH')) exit;

$settings = get_option('MK20_cfr_settings');
$year = isset($settings['year']) ? $settings['year'] : current_time('Y');
$dates = isset($settings['dates']) ? $settings['dates'] : array();
$default_color = isset($settings['default_color']) ? $settings['default_color'] : '#ff6b6b';
$show_spanish_holidays = isset($settings['show_spanish_holidays']) ? $settings['show_spanish_holidays'] : true;
$header_color_1 = isset($settings['header_color_1']) ? $settings['header_color_1'] : '#667eea';
$header_color_2 = isset($settings['header_color_2']) ? $settings['header_color_2'] : '#764ba2';
?>

<div class="wrap MK20-cfr-admin-wrap">
    <h1><?php echo esc_html__('📅 Relevant Dates Calendar', 'calendario-fechas-relevantes'); ?></h1>
    
    <form method="post" action="options.php" id="MK20-cfr-settings-form">
        <?php settings_fields('MK20_cfr_settings_group'); ?>
        
        <div class="MK20-cfr-admin-container">
            
            <div class="MK20-cfr-admin-section">
                <h2><?php echo esc_html__('⚙️ General Settings', 'calendario-fechas-relevantes'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="MK20_cfr_year"><?php esc_html_e('Calendar Year', 'calendario-fechas-relevantes'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="MK20_cfr_year" 
                                   name="MK20_cfr_settings[year]" 
                                   value="<?php echo esc_attr($year); ?>" 
                                   min="2000" 
                                   max="2100" 
                                   class="regular-text">
                            <p class="description"><?php esc_html_e('Select the year to display in the calendar', 'calendario-fechas-relevantes'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="MK20_cfr_default_color"><?php esc_html_e('Default Color', 'calendario-fechas-relevantes'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="MK20_cfr_default_color" 
                                   name="MK20_cfr_settings[default_color]" 
                                   value="<?php echo esc_attr($default_color); ?>" 
                                   class="MK20-cfr-color-picker">
                            <p class="description"><?php esc_html_e('Default color for relevant dates', 'calendario-fechas-relevantes'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="MK20_cfr_header_color_1"><?php esc_html_e('Month Header Color 1', 'calendario-fechas-relevantes'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="MK20_cfr_header_color_1" 
                                   name="MK20_cfr_settings[header_color_1]" 
                                   value="<?php echo esc_attr($header_color_1); ?>" 
                                   class="MK20-cfr-color-picker">
                            <p class="description"><?php esc_html_e('First color of the gradient for month headers', 'calendario-fechas-relevantes'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="MK20_cfr_header_color_2"><?php esc_html_e('Month Header Color 2', 'calendario-fechas-relevantes'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="MK20_cfr_header_color_2" 
                                   name="MK20_cfr_settings[header_color_2]" 
                                   value="<?php echo esc_attr($header_color_2); ?>" 
                                   class="MK20-cfr-color-picker">
                            <p class="description"><?php esc_html_e('Second color of the gradient for month headers', 'calendario-fechas-relevantes'); ?></p>
                            
                            <div class="MK20-cfr-gradient-preview" 
                                 style="
                                     background: linear-gradient(135deg, <?php echo esc_attr($header_color_1); ?> 0%, <?php echo esc_attr($header_color_2); ?> 100%);
                                     height: 50px;
                                     border-radius: 5px;
                                     margin-top: 10px;
                                     border: 2px solid #ddd;
                                     display: flex;
                                     align-items: center;
                                     justify-content: center;
                                     color: white;
                                     font-weight: bold;
                                     text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                                 ">
                                <?php esc_html_e('Preview', 'calendario-fechas-relevantes'); ?>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="MK20_cfr_show_holidays"><?php esc_html_e('Show Spanish Holidays', 'calendario-fechas-relevantes'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="MK20_cfr_show_holidays" 
                                       name="MK20_cfr_settings[show_spanish_holidays]" 
                                       value="1"
                                       <?php checked($show_spanish_holidays, true); ?>>
                                <?php esc_html_e('Automatically mark Spanish national holidays in red', 'calendario-fechas-relevantes'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Includes fixed holidays and Holy Week (calculated automatically)', 'calendario-fechas-relevantes'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="MK20-cfr-admin-section">
                <h2><?php echo esc_html__('📌 Custom Relevant Dates', 'calendario-fechas-relevantes'); ?></h2>
                
                <div id="MK20-cfr-dates-container">
                    <?php 
                    if (!empty($dates)) {
                        foreach ($dates as $index => $date) {
                            ?>
                            <div class="MK20-cfr-date-row">
                                <input type="date" 
                                       name="MK20_cfr_settings[dates][<?php echo esc_attr($index); ?>][date]"
                                       value="<?php echo isset($date['date']) ? esc_attr($date['date']) : ''; ?>" 
                                       class="MK20-cfr-date-input" 
                                       required>
                                
                                <textarea name="MK20_cfr_settings[dates][<?php echo esc_attr($index); ?>][description]" 
                                          placeholder="<?php esc_attr_e('E.g.: Alzheimer\'s Day', 'calendario-fechas-relevantes'); ?>" 
                                          class="MK20-cfr-description-input" 
                                          required><?php echo isset($date['description']) ? esc_textarea($date['description']) : ''; ?></textarea>
                                
                                <input type="text" 
                                       name="MK20_cfr_settings[dates][<?php echo esc_attr($index); ?>][color]" 
                                       value="<?php echo isset($date['color']) ? esc_attr($date['color']) : esc_attr($default_color); ?>" 
                                       class="MK20-cfr-color-picker MK20-cfr-date-color">
                                
                                <button type="button" class="button MK20-cfr-remove-date">❌ <?php esc_html_e('Delete', 'calendario-fechas-relevantes'); ?></button>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                
                <button type="button" id="MK20-cfr-add-date" class="button button-secondary">➕ <?php esc_html_e('Add Date', 'calendario-fechas-relevantes'); ?></button>
            </div>
            
            <div class="MK20-cfr-admin-section">
                <h2><?php echo esc_html__('🔗 Shortcode', 'calendario-fechas-relevantes'); ?></h2>
                <p><?php esc_html_e('Use this shortcode to display the calendar on any page or post:', 'calendario-fechas-relevantes'); ?></p>
                <code class="MK20-cfr-shortcode">[calendario_fechas]</code>
                <p class="description">
                    <?php
                    /* translators: %s: shortcode example with year parameter */
                    printf(
                        /* translators: %s: shortcode example with year parameter */
                        esc_html__('You can also specify a year: %s', 'calendario-fechas-relevantes'),
                        '<code>[calendario_fechas year="2024"]</code>'
                    );
                    ?>                    
                </p>
            </div>
            
        </div>
        
        <?php submit_button(__('💾 Save Settings', 'calendario-fechas-relevantes')); ?>
    </form>
</div>

<template id="MK20-cfr-date-row-template">
    <div class="MK20-cfr-date-row">
        <input type="date" name="MK20_cfr_settings[dates][INDEX][date]" class="MK20-cfr-date-input" required>
        
        <textarea name="MK20_cfr_settings[dates][INDEX][description]" 
                  placeholder="<?php esc_attr_e('E.g.: Alzheimer\'s Day', 'calendario-fechas-relevantes'); ?>" 
                  class="MK20-cfr-description-input" 
                  required></textarea>
        
        <input type="text" 
               name="MK20_cfr_settings[dates][INDEX][color]" 
               value="<?php echo esc_attr($default_color); ?>" 
               class="MK20-cfr-color-picker MK20-cfr-date-color">
        
        <button type="button" class="button MK20-cfr-remove-date">❌ <?php esc_html_e('Delete', 'calendario-fechas-relevantes'); ?></button>
    </div>
</template>