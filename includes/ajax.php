<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sincroniza as bibliotecas consultando a API e atualiza o banco de dados.
 */
function bunny_stream_sync() {
    global $wpdb;
    $options = get_option( 'bunny_stream_settings' );
    $api_key = $options['api_key'] ?? '';

    if ( empty( $api_key ) ) {
        wp_send_json( [ 'error' => 'API Key não configurada.' ], 400 );
    }

    $url = 'https://api.bunny.net/videolibrary';
    $response = wp_remote_get( $url, [
        'headers' => [
            'AccessKey' => $api_key,
            'Accept'    => 'application/json',
        ],
        'timeout' => 10,
    ] );

    $body = wp_remote_retrieve_body( $response );
    $libraries = json_decode( $body, true );

    if ( ! is_array( $libraries ) ) {
        wp_send_json( [ 'error' => 'Erro ao processar a resposta da API.' ], 500 );
    }

    $table_name = $wpdb->prefix . "bunny_libraries";
    $api_ids    = [];

    foreach ( $libraries as $library ) {
        $id          = intval( $library['Id'] );
        $name        = sanitize_text_field( $library['Name'] );
        $ro_api      = sanitize_text_field( $library['ApiKey'] );
        $video_count = intval( $library['VideoCount'] );
        $api_ids[]   = $id;

        $wpdb->replace(
            $table_name,
            [
                'id'          => $id,
                'name'        => $name,
                'api_key'     => $ro_api,
                'video_count' => $video_count,
            ],
            [
                '%d',
                '%s',
                '%s',
                '%d'
            ]
        );
    }

    if ( ! empty( $api_ids ) ) {
        $ids_in = implode( ',', array_map( 'intval', $api_ids ) );
        $wpdb->query( "DELETE FROM $table_name WHERE id NOT IN ($ids_in)" );
    } else {
        $wpdb->query( "TRUNCATE TABLE $table_name" );
    }

    wp_send_json( [ 'success' => 'Bibliotecas sincronizadas.' ] );
}
add_action( 'wp_ajax_bunny_stream_sync', 'bunny_stream_sync' );

/**
 * Retorna as bibliotecas salvas no banco de dados.
 */
function bunny_stream_get_libraries() {
    global $wpdb;
    $table_name = $wpdb->prefix . "bunny_libraries";
    $libraries = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
    wp_send_json( $libraries );
}
add_action( 'wp_ajax_bunny_stream_get_libraries', 'bunny_stream_get_libraries' );

/**
 * Sincroniza os vídeos de uma biblioteca consultando a API e salvando-os no banco de dados.
 */
function bunny_stream_sync_videos() {
    global $wpdb;
    $library_id = $_GET['library_id'] ?? '';
    $api_key    = $_GET['api_key'] ?? '';

    if ( empty( $library_id ) || empty( $api_key ) ) {
        wp_send_json( [ 'error' => 'ID da biblioteca ou API Key não informados.' ], 400 );
    }

    $url = "https://video.bunnycdn.com/library/{$library_id}/videos";
    $response = wp_remote_get( $url, [
        'headers' => [
            'AccessKey' => $api_key,
            'Accept'    => 'application/json',
        ],
        'timeout' => 10,
    ] );

    $body = wp_remote_retrieve_body( $response );
    $videos = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        wp_send_json( [
            'error' => 'Erro ao processar JSON: ' . json_last_error_msg(),
            'body'  => $body
        ], 500 );
    }

    if ( ! is_array( $videos ) ) {
        wp_send_json( [
            'error' => 'Resposta da API de vídeos não é um array.',
            'body'  => $body
        ], 500 );
    }

    $items = isset( $videos['items'] ) && is_array( $videos['items'] ) ? $videos['items'] : $videos;
    $table_name = $wpdb->prefix . "bunny_videos";
    $api_guids  = [];

    foreach ( $items as $video ) {
        $guid         = sanitize_text_field( $video['guid'] );
        $title        = sanitize_text_field( $video['title'] );
        $length       = intval( $video['length'] );
        $dateUploaded = isset( $video['dateUploaded'] ) ? date( "Y-m-d H:i:s", strtotime( $video['dateUploaded'] ) ) : null;
        $api_guids[]  = $guid;

        $wpdb->replace(
            $table_name,
            [
                'library_id'    => intval( $library_id ),
                'guid'          => $guid,
                'title'         => $title,
                'length'        => $length,
                'date_uploaded' => $dateUploaded,
            ],
            [
                '%d',
                '%s',
                '%s',
                '%d',
                '%s'
            ]
        );
    }

    if ( ! empty( $api_guids ) ) {
        $guids_in = "'" . implode( "','", array_map( 'esc_sql', $api_guids ) ) . "'";
        $wpdb->query( "DELETE FROM $table_name WHERE library_id = " . intval( $library_id ) . " AND guid NOT IN ($guids_in)" );
    } else {
        $wpdb->query( "DELETE FROM $table_name WHERE library_id = " . intval( $library_id ) );
    }

    $saved_videos = $wpdb->get_results( "SELECT * FROM $table_name WHERE library_id = " . intval( $library_id ), ARRAY_A );
    wp_send_json( [ 'items' => $saved_videos ] );
}
add_action( 'wp_ajax_bunny_stream_sync_videos', 'bunny_stream_sync_videos' );
