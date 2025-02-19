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
    $form = '<form method="get" style="max-width: 800px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    $form .= '<input type="hidden" name="library_id" value="' . esc_attr($library_id) . '">';

    $form .= '<div style="display: flex; align-items: center; gap: 10px;">';

// Campo de pesquisa
    $form .= '<div style="flex: 2; position: relative;">';
    $form .= '<span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">&#128269;</span>'; // Ícone de lupa
    $form .= '<input type="text" id="bsv_search" name="bsv_search" value="' . esc_attr($search) . '" placeholder="Pesquisar" style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 4px;">';
    $form .= '</div>';

// Campo de data início
    $form .= '<div style="flex: 1; position: relative;">';
    $form .= '<span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">&#128197;</span>'; // Ícone de calendário
    $form .= '<input type="date" id="bsv_start_date" name="bsv_start_date" value="' . esc_attr($start_date) . '" style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 4px;">';
    $form .= '</div>';

// Campo de data fim
    $form .= '<div style="flex: 1; position: relative;">';
    $form .= '<span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">&#128197;</span>'; // Ícone de calendário
    $form .= '<input type="date" id="bsv_end_date" name="bsv_end_date" value="' . esc_attr($end_date) . '" style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 4px;">';
    $form .= '</div>';

// Botão de filtrar
    $form .= '<button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">&#128269; Filtrar</button>';

    $form .= '</div>';
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
    $output .= '<div class="bunny-stream-videos" style="max-width: 1200px; margin: 20px auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">';

    // Loop nos vídeos e gera um embed (iframe) para cada um
    foreach ($videos as $video) {
        // URL padrão do Bunny para exibir o vídeo
        $embed_url = sprintf('https://iframe.mediadelivery.net/embed/%d/%s?autoplay=false&loop=false&muted=false&preload=false&responsive=true', $library_id, esc_attr($video['guid']));

        $output .= '<div class="bunny-video" style="width: 100%;">';
        $output .= '<div style="position: relative; padding-top: 56.25%; background-color: #f0f0f0; border-radius: 8px; overflow: hidden; margin-bottom: 10px;">';
        $output .= '<iframe src="' . esc_url($embed_url) . '" loading="lazy" style="border: 0; position: absolute; top: 0; left: 0; height: 100%; width: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe>';
        $output .= '</div>';
        $output .= '<h3 style="margin: 0; font-size: 1rem; line-height: 1.3; max-height: 2.6em; overflow: hidden; text-overflow: ellipsis; color: #565656;">' . esc_html($video['title']) . '</h3>';
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode( 'bunny_stream_videos', 'bunny_stream_videos_shortcode' );
