# Bunny Stream Plugin

O Bunny Stream Plugin integra o Bunny Stream ao seu site WordPress. Ele permite a sincronização de bibliotecas e vídeos via API do Bunny Stream, armazenando os dados em tabelas personalizadas no banco de dados, e oferece uma interface administrativa para gerenciar essa integração.

## Estrutura do Plugin

```bash
bunny-stream/
  ├── bunny-stream.php
  ├── uninstall.php
  ├── includes/
    ├── activation.php
    ├── admin.php
    ├── ajax.php
    └── helpers.php
  └── assets/
    └── js/
      └── admin.js
```

## Descrição dos Arquivos

1. **bunny-stream.php:** Arquivo principal que contém as informações do plugin e inclui os demais arquivos responsáveis pelas funcionalidades.

   - Definir as informações do plugin (nome, versão, autor, etc).
   - Incluir arquivos de ativação, administração, AJAX e helpers.
   - Registrar o hook de ativação para criar as tabelas necessárias.


2. **uninstall.php:** Arquivo executado automaticamente pelo WordPress quando o plugin é desinstalado.

   - Verificar se a desinstalação está sendo executada corretamente usando WP_UNINSTALL_PLUGIN. 
   - Remover as tabelas personalizadas (bunny_libraries e bunny_videos) do banco de dados.
   - Excluir as opções salvas pelo plugin (por exemplo, bunny_stream_settings).


3. **includes/activation.php:** Gerencia a criação das tabelas no banco de dados durante a ativação do plugin.


4. **includes/admin.php:** Responsável pela interface administrativa do plugin, incluindo o registro de configurações, criação da página de opções e enfileiramento dos scripts.

   - Registrar as configurações e seções para o painel de administração.
   - Exibir o campo de entrada para a API Key.
   - Adicionar a página do plugin no menu administrativo do WordPress.
   - Enfileirar o arquivo JavaScript (admin.js) para uso na área administrativa.


5. **includes/ajax.php:** Contém os handlers AJAX responsáveis pela comunicação com a API do Bunny Stream e a atualização dos dados no banco.


6. **includes/helpers.php:** Destinado a funções auxiliares que podem ser reutilizadas em diversas partes do plugin.


7. **assets/js/admin.js:** Arquivo JavaScript que gerencia as interações na área administrativa.