<?php
// Verifica se o WordPress está chamando este arquivo via desinstalação
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Define os nomes das tabelas utilizadas pelo plugin
$libraries_table = $wpdb->prefix . 'bunny_libraries';
$videos_table    = $wpdb->prefix . 'bunny_videos';

// Remove as tabelas, se existirem
$wpdb->query( "DROP TABLE IF EXISTS {$libraries_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$videos_table}" );

// Opcional: remove as opções salvas
delete_option( 'bunny_stream_settings' );
