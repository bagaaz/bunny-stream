<?php
/**
 * Plugin Name: Bunny Stream
 * Plugin URI: https://bunnycdn.com
 * Description: Plugin para integrar Bunny Stream ao seu WordPress.
 * Version: 1.4.0
 * Author: DevConecta
 * Author URI: https://devconecta.com.br
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constantes se necessário (ex.: caminho para a pasta includes)
define( 'BUNNY_STREAM_PATH', plugin_dir_path( __FILE__ ) );

// Inclui os arquivos de funcionalidades
require_once BUNNY_STREAM_PATH . 'includes/activation.php';
require_once BUNNY_STREAM_PATH . 'includes/admin.php';
require_once BUNNY_STREAM_PATH . 'includes/ajax.php';
require_once BUNNY_STREAM_PATH . 'includes/helpers.php';

// Registra as funções de ativação
register_activation_hook( __FILE__, 'bunny_stream_create_tables' );
