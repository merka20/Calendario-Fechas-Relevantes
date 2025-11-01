<?php
/**
 * Archivo de desinstalación del plugin
 * Se ejecuta cuando el plugin se elimina desde WordPress
 * 
 * @package Calendario Fechas Relevantes
 */

// Si no se llama desde WordPress, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Eliminar opciones de la base de datos
delete_option('MK20_cfr_settings');

// Si usas multisitio, eliminar también de todos los sitios
if (is_multisite()) {
    global $wpdb;
    
    // Obtener todos los blog IDs
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    // Justificación: Esta es una operación de limpieza única durante la desinstalación.
    // No tiene sentido cachear datos que se van a eliminar.
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('MK20_cfr_settings');
    }
    
    switch_to_blog($original_blog_id);
}

// Limpiar transients si los usaras en el futuro
delete_transient('MK20_cfr_cache');

// Opcional: Eliminar metadatos de posts/páginas si los usaras
// delete_post_meta_by_key('MK20_cfr_custom_meta');