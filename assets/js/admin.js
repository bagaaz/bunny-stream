document.addEventListener("DOMContentLoaded", function() {
    loadLibraries();

    document.getElementById("sync-libraries").addEventListener("click", function () {
        var button = this;
        button.innerHTML = "Sincronizando...";
        button.disabled = true;

        fetch(ajaxurl + "?action=bunny_stream_sync")
            .then(response => response.json())
            .then(data => {
                button.innerHTML = "Sincronizar Bibliotecas";
                button.disabled = false;
                loadLibraries();
            })
            .catch(error => {
                button.innerHTML = "Sincronizar Bibliotecas";
                button.disabled = false;
                document.getElementById("bunny-libraries").innerHTML = "<p style='color: red;'>Erro ao sincronizar bibliotecas.</p>";
            });
    });
});

// Função para carregar as bibliotecas salvas no banco de dados
function loadLibraries() {
    fetch(ajaxurl + "?action=bunny_stream_get_libraries")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById("bunny-libraries").innerHTML = "<p style='color: red;'>" + data.error + "</p>";
            } else if (data.length > 0) {
                let output = "<ul>";
                data.forEach(library => {
                    output += `<li>
                        <a href="#" class="library-link" data-id="${library.id}" data-api-key="${library.api_key}">
                            <strong>${library.name}</strong> - ${library.video_count} vídeos
                        </a>
                    </li>`;
                });
                output += "</ul>";
                document.getElementById("bunny-libraries").innerHTML = output;

                document.querySelectorAll(".library-link").forEach(link => {
                    link.addEventListener("click", function (e) {
                        e.preventDefault();
                        let libraryId = this.getAttribute("data-id");
                        let apiKey = this.getAttribute("data-api-key");
                        fetchVideos(libraryId, apiKey);
                    });
                });
            } else {
                document.getElementById("bunny-libraries").innerHTML = "<p>Nenhuma biblioteca encontrada.</p>";
            }
        });
}

// Função para sincronizar e obter os vídeos da biblioteca
function fetchVideos(libraryId, apiKey) {
    document.getElementById("bunny-videos").innerHTML = "Carregando vídeos...";

    fetch(ajaxurl + "?action=bunny_stream_sync_videos&library_id=" + libraryId + "&api_key=" + apiKey)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById("bunny-videos").innerHTML = "<p style='color: red;'>" + data.error + "</p>";
            } else if (data.items && Array.isArray(data.items) && data.items.length > 0) {
                let output = "<ul>";
                data.items.forEach(video => {
                    output += `<li>
                        <strong>${video.title}</strong> (Duração: ${video.length} segundos)
                        <br>
                        <a href="https://iframe.mediadelivery.net/play/${libraryId}/${video.guid}" target="_blank">Assistir Vídeo</a>
                    </li>`;
                });
                output += "</ul>";
                document.getElementById("bunny-videos").innerHTML = output;
            } else {
                document.getElementById("bunny-videos").innerHTML = "<p style='color: orange;'>Nenhum vídeo encontrado.</p>";
            }
        })
        .catch(error => {
            console.error("Erro ao obter os vídeos:", error);
            document.getElementById("bunny-videos").innerHTML = "<p style='color: red;'>Erro ao obter os vídeos.</p>";
        });
}
