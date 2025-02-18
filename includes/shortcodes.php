<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode para listar vídeos de uma biblioteca do Bunny Stream com filtro de pesquisa.
 *
 * Uso: [bunny_stream_videos library_id="123"]
 *
 * @param array $atts Atributos do shortcode.
 * @return string HTML para exibir os vídeos com filtros.
 */
function bunny_stream_videos_shortcode( $atts ) {
    global $wpdb;

    // Define atributos padrão e sobrescreve com os fornecidos
    $atts = shortcode_atts( array(
        'library_id' => '',
    ), $atts, 'bunny_stream_videos' );

    $library_id = intval( $atts['library_id'] );
    if ( empty( $library_id ) ) {
        return '<p>Por favor, informe um ID válido de biblioteca.</p>';
    }

    // Obtém os filtros enviados (via GET)
    $search     = isset( $_GET['bsv_search'] ) ? sanitize_text_field( $_GET['bsv_search'] ) : '';
    $start_date = isset( $_GET['bsv_start_date'] ) ? sanitize_text_field( $_GET['bsv_start_date'] ) : '';
    $end_date   = isset( $_GET['bsv_end_date'] ) ? sanitize_text_field( $_GET['bsv_end_date'] ) : '';

    // Formulário de filtro
    $form = '<form method="get" style="margin-bottom:20px;">';
    // Mantenha o library_id (pode ser passado via hidden ou como parte da URL)
    $form .= '<input type="hidden" name="library_id" value="' . $library_id . '">';
    $form .= '<label>Pesquisar: <input type="text" name="bsv_search" value="' . esc_attr( $search ) . '"></label> ';
    $form .= '<label>Data Início: <input type="date" name="bsv_start_date" value="' . esc_attr( $start_date ) . '"></label> ';
    $form .= '<label>Data Fim: <input type="date" name="bsv_end_date" value="' . esc_attr( $end_date ) . '"></label> ';
    $form .= '<button type="submit">Filtrar</button>';
    $form .= '</form>';

    // Monta a query SQL com os filtros
    $table_name = $wpdb->prefix . 'bunny_videos';
    $query = "SELECT * FROM $table_name WHERE library_id = %d";
    $params = array( $library_id );

    if ( ! empty( $search ) ) {
        $query .= " AND title LIKE %s";
        $params[] = '%' . $wpdb->esc_like( $search ) . '%';
    }
    if ( ! empty( $start_date ) ) {
        $query .= " AND date_uploaded >= %s";
        $params[] = $start_date . ' 00:00:00';
    }
    if ( ! empty( $end_date ) ) {
        $query .= " AND date_uploaded <= %s";
        $params[] = $end_date . ' 23:59:59';
    }

    $query = $wpdb->prepare( $query, $params );
    $videos = $wpdb->get_results( $query, ARRAY_A );

    // Se não encontrar vídeos, retorna o formulário e mensagem de aviso
    if ( empty( $videos ) ) {
        return $form . '<p>Nenhum vídeo encontrado para esta biblioteca.</p>';
    }

    // Inicia o output com o formulário
    $output = $form;
    $output .= '<div class="bunny-stream-videos">';

    // Loop nos vídeos e gera um embed (iframe) para cada um
    foreach ( $videos as $video ) {
        // URL padrão do Bunny para exibir o vídeo
        $embed_url = sprintf( 'https://iframe.mediadelivery.net/play/%d/%s', $library_id, esc_attr( $video['guid'] ) );

        $output .= '<div class="bunny-video" style="margin-bottom: 20px;">';
        $output .= '<h3>' . esc_html( $video['title'] ) . '</h3>';
        $output .= '<iframe src="' . esc_url( $embed_url ) . '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode( 'bunny_stream_videos', 'bunny_stream_videos_shortcode' );
