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

// Função para carregar as bibliotecas e exibi-las em uma tabela
function loadLibraries() {
    fetch(ajaxurl + "?action=bunny_stream_get_libraries")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById("bunny-libraries").innerHTML = "<p style='color: red;'>" + data.error + "</p>";
            } else if (data.length > 0) {
                let output = `
                    <table border="1" cellspacing="0" cellpadding="5">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da Biblioteca</th>
                                <th>Token Authentication Key</th>
                                <th>CDN Hostname</th>
                                <th>Quantidade de Vídeos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>`;
                data.forEach(library => {
                    output += `
                        <tr>
                            <td>${library.id}</td>
                            <td>${library.name}</td>
                            <td><input type="text" class="token-key" data-library-id="${library.id}" value="${library.token_auth_key ? library.token_auth_key : ''}" style="width:150px;"></td>
                            <td><input type="text" class="cdn-hostname" data-library-id="${library.id}" value="${library.cdn_hostname ? library.cdn_hostname : ''}" style="width:150px;"></td>
                            <td>${library.video_count}</td>
                            <td>
                                <button class="update-library" data-library-id="${library.id}">Atualizar</button>
                                <button class="import-videos" data-library-id="${library.id}" data-api-key="${library.api_key}">Importar Vídeos</button>
                            </td>
                        </tr>`;
                });
                output += `
                        </tbody>
                    </table>`;
                document.getElementById("bunny-libraries").innerHTML = output;

                // Adiciona o listener para os botões de atualizar
                document.querySelectorAll(".update-library").forEach(button => {
                    button.addEventListener("click", function () {
                        const libraryId = this.getAttribute("data-library-id");
                        const tokenKey = document.querySelector(`.token-key[data-library-id="${libraryId}"]`).value;
                        const cdnHostname = document.querySelector(`.cdn-hostname[data-library-id="${libraryId}"]`).value;

                        // Envia os dados via AJAX para atualizar a biblioteca
                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                            },
                            body: new URLSearchParams({
                                action: "bunny_stream_update_library_details",
                                library_id: libraryId,
                                token_auth_key: tokenKey,
                                cdn_hostname: cdnHostname
                            })
                        })
                            .then(response => response.json())
                            .then(result => {
                                if(result.success) {
                                    alert("Biblioteca atualizada com sucesso!");
                                    loadLibraries();
                                } else {
                                    alert("Erro: " + result.data);
                                }
                            })
                            .catch(error => {
                                alert("Erro ao atualizar: " + error);
                            });
                    });
                });

                // Adiciona o listener para os botões de importar vídeos
                document.querySelectorAll(".import-videos").forEach(button => {
                    button.addEventListener("click", function () {
                        const libraryId = this.getAttribute("data-library-id");
                        const apiKey = this.getAttribute("data-api-key");
                        importVideos(libraryId, apiKey);
                    });
                });
            } else {
                document.getElementById("bunny-libraries").innerHTML = "<p>Nenhuma biblioteca encontrada.</p>";
            }
        });
}

// Função para importar vídeos da biblioteca e exibir mensagem com a quantidade importada
function importVideos(libraryId, apiKey) {
    document.getElementById("bunny-videos").innerHTML = "Importando vídeos...";
    fetch(ajaxurl + "?action=bunny_stream_sync_videos&library_id=" + libraryId + "&api_key=" + apiKey)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById("bunny-videos").innerHTML = "<p style='color: red;'>" + data.error + "</p>";
            } else if (data.items && Array.isArray(data.items)) {
                const totalVideos = data.items.length;
                document.getElementById("bunny-videos").innerHTML = "<p>Foram importados " + totalVideos + " vídeos desta biblioteca.</p>";
            } else {
                document.getElementById("bunny-videos").innerHTML = "<p style='color: orange;'>Nenhum vídeo encontrado para importação.</p>";
            }
        })
        .catch(error => {
            console.error("Erro ao importar os vídeos:", error);
            document.getElementById("bunny-videos").innerHTML = "<p style='color: red;'>Erro ao importar os vídeos.</p>";
        });
}
