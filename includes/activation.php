<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Cria as tabelas necessárias do plugin.
 */
function bunny_stream_create_tables() {
    bunny_stream_create_libraries_table();
    bunny_stream_create_videos_table();
}

/**
 * Cria a tabela de bibliotecas.
 */
function bunny_stream_create_libraries_table() {
    global $wpdb;
    $table_name      = $wpdb->prefix . "bunny_libraries";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL,
        token_auth_key VARCHAR(255) DEFAULT NULL,
        cdn_hostname VARCHAR(255) DEFAULT NULL,
        video_count INT DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

/**
 * Cria a tabela de vídeos.
 */
function bunny_stream_create_videos_table() {
    global $wpdb;
    $table_name      = $wpdb->prefix . "bunny_videos";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        library_id INT NOT NULL,
        guid VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        length INT DEFAULT 0,
        date_uploaded DATETIME NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_video (library_id, guid)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
