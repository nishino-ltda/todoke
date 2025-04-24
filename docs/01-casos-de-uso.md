# TODOKE: Etapa 1 - Casos de Uso

## Atores

*   **Aurora, a entregador Independente:** Uma entregador experiente que busca mais autonomia e melhores ganhos. Utiliza o app Ionic.
*   **BistroTech, a Rede de Restaurantes:** Uma rede de restaurantes que deseja otimizar suas entregas e reduzir custos. Utiliza o painel administrativo web (Laravel).
*   **DroneExpress, a Startup de Entregas com Drones:** Uma empresa inovadora que utiliza drones integrados à plataforma TODOKE. Utiliza a API e possivelmente um painel administrativo.
*   **E-ComShop, a Loja Online de Artesanato:** Uma loja online que busca integrar um sistema de entregas eficiente e personalizado. Utiliza a API.
*   **LogisMaster, a Empresa de Logística:** Uma empresa de logística que busca otimizar suas operações e expandir seus serviços. Utiliza o painel administrativo web (Laravel) e a API.
*   **Cliente Urbano Consciente (EcoCliente):** Um cliente final que valoriza a sustentabilidade e a transparência nas entregas. Utiliza o app Ionic.
*   **Morador Remoto (ConectRural):** Um cliente que reside em área rural e necessita de acesso a serviços de entrega eficientes. Utiliza o app Ionic.
*   **AdminSys, o Administrador do Sistema:** Responsável pela configuração, monitoramento e manutenção da plataforma TODOKE. Utiliza o painel administrativo web (Laravel) e ferramentas de monitoramento (Grafana).
*   **NodeParceiro, o Sistema Externo:** Um sistema de um parceiro que se integra ao TODOKE via API ou eventos.

## Casos de Uso

### [❌] 1.1 Aurora se Cadastra e Configura seu Perfil

*   **Atores:** Aurora
*   **Descrição:** Aurora se cadastra na plataforma, configura seu perfil, define áreas de atuação e horários, e gerencia sua disponibilidade.
*   **Fluxo Principal:**
    1.  Aurora baixa e instala o app TODOKE (Ionic).
    2.  Aurora preenche o formulário de registro (dados pessoais, veículo, CNH, etc.).
    3.  Sistema (Central Node) valida os dados e envia email/SMS de verificação.
    4.  Aurora confirma a verificação.
    5.  Aurora acessa o app, escolhe o perfil "Entregador".
    6.  Aurora configura suas áreas de atuação preferenciais no mapa.
    7.  Aurora define seus horários de trabalho padrão.
    8.  Aurora ativa seu status como "Online" para receber solicitações.
    9.  Aurora pode alternar seu status (Online/Offline) a qualquer momento.
    10. Aurora edita seus dados pessoais e foto de perfil.
*   **Fluxo Alternativo:**
    *   Se a validação de dados falhar, o sistema informa o erro específico.
    *   Se a verificação de email/SMS não for concluída, a conta fica pendente.
    *   Se Aurora tentar ficar online fora dos horários definidos, o sistema pode alertá-la (configurável).

### [❌] 1.2 EcoCliente Solicita uma Entrega

*   **Atores:** EcoCliente
*   **Descrição:** EcoCliente utiliza o app TODOKE para solicitar uma entrega, definindo pontos de coleta e entrega, e escolhendo opções.
*   **Fluxo Principal:**
    1.  EcoCliente abre o app TODOKE (Ionic) e seleciona "Solicitar Entrega".
    2.  EcoCliente define o ponto de coleta no mapa (busca por endereço ou marcação direta).
    3.  EcoCliente define o ponto de entrega no mapa.
    4.  EcoCliente informa detalhes do item a ser entregue (tamanho, peso estimado, instruções).
    5.  Sistema (Central Node + Docker Node API) calcula o preço e tempo estimado, considerando distância, trânsito (se disponível) e fatores dinâmicos.
    6.  EcoCliente visualiza as opções de entrega (Normal, Expressa, Sustentável) com preços e tempos.
    7.  EcoCliente seleciona uma opção e confirma o método de pagamento (pré-cadastrado ou novo).
    8.  Sistema (Central Node) busca o entregador mais adequado (Aurora) baseado em proximidade, disponibilidade e tipo de veículo.
    9.  EcoCliente recebe a confirmação da solicitação e os dados do entregador (nome, foto, veículo, avaliação).
*   **Fluxo Alternativo:**
    *   Se o endereço estiver fora da área de cobertura, o sistema informa e não permite prosseguir.
    *   Se o cálculo de preço/tempo falhar, o sistema exibe uma mensagem de erro.
    *   Se o pagamento falhar, o sistema informa e permite tentar novamente ou escolher outro método.
    *   Se nenhum entregador for encontrado, o sistema informa e pode sugerir agendamento ou tentar novamente mais tarde.

### [❌] 1.3 Aurora Aceita e Realiza a Entrega

*   **Atores:** Aurora, EcoCliente
*   **Descrição:** Aurora recebe a notificação da solicitação, aceita, realiza a coleta e a entrega, atualizando o status em tempo real.
*   **Fluxo Principal:**
    1.  Aurora (Online no app Ionic) recebe uma notificação push e sonora de nova solicitação.
    2.  Aurora visualiza os detalhes da solicitação (pontos, item, valor estimado).
    3.  Aurora aceita a solicitação dentro do tempo limite.
    4.  Sistema (Central Node) atualiza o status da entrega e notifica EcoCliente.
    5.  Aurora utiliza a navegação turn-by-turn integrada no app para ir ao ponto de coleta.
    6.  No ponto de coleta, Aurora confirma a coleta (pode envolver código ou foto). Sistema atualiza status e notifica EcoCliente.
    7.  Aurora utiliza a navegação para ir ao ponto de entrega.
    8.  No ponto de entrega, Aurora confirma a entrega (pode envolver assinatura digital ou foto). Sistema atualiza status e notifica EcoCliente.
    9.  Aurora e EcoCliente são convidados a avaliar a experiência.
*   **Fluxo Alternativo:**
    *   Se Aurora recusar ou não aceitar a tempo, o sistema busca outro entregador.
    *   Se Aurora encontrar um problema (ocorrência), ela registra no app (ex: endereço não encontrado, cliente ausente). Sistema notifica EcoCliente e/ou suporte.
    *   Se a conexão de Aurora falhar, o app armazena as atualizações de status localmente (modo offline) e sincroniza quando a conexão retornar.

### [❌] 1.4 EcoCliente Acompanha a Entrega e Avalia

*   **Atores:** EcoCliente, Aurora
*   **Descrição:** EcoCliente acompanha o progresso da entrega em tempo real no mapa, se comunica com Aurora e avalia o serviço.
*   **Fluxo Principal:**
    1.  Após a confirmação, EcoCliente acessa a tela de acompanhamento no app Ionic.
    2.  EcoCliente visualiza a posição de Aurora no mapa em tempo real e o tempo estimado de chegada atualizado.
    3.  EcoCliente recebe notificações push sobre mudanças de status (Coletado, A Caminho, Entregue).
    4.  EcoCliente pode iniciar um chat com Aurora para enviar instruções adicionais ou tirar dúvidas.
    5.  Após a entrega, EcoCliente avalia Aurora (estrelas e comentário).
*   **Fluxo Alternativo:**
    *   Se o rastreamento falhar temporariamente, o app exibe a última localização conhecida e uma mensagem informativa.
    *   Se EcoCliente precisar cancelar a entrega (antes da coleta), ele pode fazê-lo pelo app (política de cancelamento aplicável).

### [❌] 1.5 BistroTech Gerencia Entregas via Painel Web

*   **Atores:** BistroTech (Gerente), Aurora
*   **Descrição:** O gerente do BistroTech utiliza o painel web (Laravel + Vue/Inertia) para criar e monitorar solicitações de entrega.
*   **Fluxo Principal:**
    1.  Gerente faz login no painel web TODOKE (`/login`).
    2.  Gerente visualiza o Dashboard (`/dashboard`) com um resumo das entregas ativas (`TdkStatWidget`), um mapa com a localização dos entregadores (`TdkMapDisplay`) e uma lista/tabela das últimas entregas (`TdkDataTable`).
    3.  Gerente navega para a seção "Criar Entrega" (`/deliveries/create`).
    4.  Gerente preenche o formulário com dados de coleta (endereço do restaurante, detalhes) e entrega (endereço do cliente, nome, telefone, instruções).
    5.  Gerente pode optar por:
        *   Deixar o sistema alocar o entregador mais próximo (padrão).
        *   Selecionar um entregador específico de sua frota (se aplicável).
        *   Agendar a entrega para um horário futuro.
    6.  Gerente confirma a solicitação. Sistema (Web Laravel -> Central Node API) processa e envia para o entregador (ex: Aurora via App Ionic).
    7.  Gerente navega para a seção "Entregas" (`/deliveries`) para monitorar o status em uma tabela (`TdkDataTable`) com filtros (status, data, entregador) e visualização no mapa (`TdkMapDisplay`). Clica em uma entrega para ver detalhes (`/deliveries/{id}`).
    8.  Gerente pode usar a função "Criar em Lote" (`/deliveries/batch-create`), fazendo upload de uma planilha (`.csv`, `.xlsx`) através de um componente (`TdkFileUpload`).
    9.  Gerente visualiza relatórios básicos na seção "Relatórios" (`/reports`) (ex: tempo médio por entregador, entregas por período).
*   **Fluxo Alternativo:**
    *   Se a validação do formulário falhar, campos inválidos são destacados com mensagens de erro.
    *   Se o upload da planilha falhar, uma mensagem de erro é exibida.
    *   Se a alocação automática falhar, a entrega fica com status "Pendente Alocação" e o gerente é notificado (ex: badge no painel).
    *   Se houver uma ocorrência registrada por Aurora, um ícone de alerta (`TdkStatusBadge`) aparece na linha da entrega na tabela, e detalhes são visíveis na página da entrega.

### [❌] 1.6 AdminSys Monitora e Gerencia a Plataforma via Painel Web

*   **Atores:** AdminSys
*   **Descrição:** O administrador do sistema utiliza o painel web (Laravel + Vue/Inertia) e ferramentas externas para monitorar a saúde, gerenciar usuários e configurações da plataforma.
*   **Fluxo Principal (Painel Web):**
    1.  AdminSys faz login no painel web TODOKE (`/login`) com credenciais de administrador.
    2.  AdminSys visualiza um Dashboard (`/admin/dashboard`) específico com métricas chave da plataforma: número de usuários ativos, entregas em andamento, status dos nodes parceiros (`TdkStatWidget`, `TdkCard`).
    3.  AdminSys navega para "Gerenciamento de Usuários" (`/admin/users`) para visualizar, editar, ativar/desativar contas de usuários (entregadores, clientes, gerentes, outros admins) usando uma tabela (`TdkDataTable`).
    4.  AdminSys navega para "Gerenciamento de Nodes Parceiros" (`/admin/nodes`) para visualizar a lista de nodes, seus status (Online/Offline/Degradado - `TdkStatusBadge`), e aprovar/rejeitar novos registros de parceiros.
    5.  AdminSys navega para "Configurações Globais" (`/admin/settings`) para ajustar parâmetros da plataforma (ex: taxas padrão, limites, configurações de API).
    6.  AdminSys acessa logs de aplicação diretamente no painel (se integrado) ou via link para ferramenta externa (Kibana/Loki).
*   **Fluxo Principal (Ferramentas Externas):**
    1.  AdminSys acessa o Grafana para monitoramento detalhado de infraestrutura (CPU, memória, rede dos containers/servidores) e performance de serviços (latência, erros, RPS da API Central, Workers, DB).
    2.  AdminSys recebe alertas configurados no Alertmanager (integrado ao Grafana/Prometheus) via Slack/Email sobre condições críticas.
    3.  AdminSys utiliza Kibana/Loki para busca e análise aprofundada de logs centralizados.
    4.  AdminSys utiliza tracing distribuído (Jaeger/Tempo) para investigar gargalos de performance em requisições complexas.
*   **Fluxo Alternativo:**
    *   Se um node parceiro estiver offline, AdminSys pode tentar reativá-lo pelo painel ou contatar o parceiro.
    *   Se um usuário reportar um problema, AdminSys usa o painel e logs para investigar a conta e atividades recentes.
    *   Se um alerta crítico for recebido, AdminSys segue o runbook documentado para resposta a incidentes.

### [❌] 1.7 Aurora Utiliza o App em Modo Offline

*   **Atores:** Aurora
*   **Descrição:** Aurora perde a conexão com a internet durante uma entrega, mas consegue continuar atualizando o status e visualizando informações essenciais.
*   **Fluxo Principal:**
    1.  Aurora está realizando uma entrega e entra em uma área sem sinal de internet.
    2.  O app Ionic detecta a perda de conexão e entra em modo offline.
    3.  Aurora chega ao ponto de coleta e confirma a coleta no app. A atualização é armazenada localmente.
    4.  O mapa (tiles cacheados) e os detalhes da entrega permanecem acessíveis.
    5.  Aurora chega ao ponto de entrega e confirma a entrega no app. A atualização é armazenada localmente.
    6.  Ao retornar para uma área com sinal, o app detecta a conexão.
    7.  O serviço de sincronização (Ionic + Central Node) envia as atualizações pendentes para o servidor.
    8.  Sistema atualiza o status real da entrega e notifica EcoCliente (se ainda não tiver sido notificado por outra via).
*   **Fluxo Alternativo:**
    *   Se o cache de mapa não cobrir a área offline, a navegação pode ser limitada.
    *   Se houver conflito de dados durante a sincronização, o sistema aplica a política de resolução definida (ex: último a escrever ganha ou notifica AdminSys).

### [❌] 1.8 LogisMaster Gerencia Regiões e Nodes via Painel Web

*   **Atores:** LogisMaster (Admin), AdminSys
*   **Descrição:** O administrador da LogisMaster utiliza o painel web (Laravel + Vue/Inertia) para gerenciar as regiões geográficas de operação e os recursos (nodes: entregadores/drones) associados. AdminSys supervisiona.
*   **Fluxo Principal:**
    1.  Admin da LogisMaster faz login no painel web TODOKE (`/login`).
    2.  Admin da LogisMaster navega para "Gerenciamento de Regiões" (`/partner/regions`).
    3.  Visualiza suas regiões atuais em uma lista/mapa (`TdkDataTable`, `TdkMapDisplay`).
    4.  Clica em "Editar Região" ou "Nova Região".
    5.  Utiliza um componente de mapa interativo (`TdkRegionEditor`) para desenhar ou ajustar o polígono que define a área geográfica de operação. Salva a região.
    6.  Admin da LogisMaster navega para "Gerenciamento de Nodes" (`/partner/nodes`).
    7.  Visualiza seus nodes (entregadores, drones) em uma tabela (`TdkDataTable`) com informações como nome, tipo, status (`TdkStatusBadge`), capacidade e região associada.
    8.  Clica em "Adicionar Node". Preenche um formulário com os detalhes do node (nome, tipo de veículo/drone, placa/identificador, capacidade de carga, habilidades especiais) e seleciona a região geográfica à qual ele pertence. Salva o node.
    9.  Admin da LogisMaster pode editar ou desativar nodes existentes.
    10. Sistema (Web Laravel -> Central Node API) valida e armazena as informações. O Central Node usa esses dados para roteamento geográfico.
    11. AdminSys (em seu painel `/admin/nodes` e `/admin/regions`) pode visualizar as regiões e nodes de todos os parceiros, e tem a capacidade de aprovar/rejeitar novos parceiros ou intervir se necessário.
*   **Fluxo Alternativo:**
    *   Se o desenho da região for inválido (ex: autointersecção), o componente `TdkRegionEditor` exibe um erro.
    *   Se tentar associar um node a uma região inexistente ou de outro parceiro, o formulário exibe um erro.
    *   Se um node ficar offline (detectado pelo Central Node via App Ionic/API do Drone), seu status é atualizado automaticamente na tabela (`TdkStatusBadge`) para "Offline".
    *   Se houver sobreposição de regiões entre parceiros, AdminSys é notificado para resolver o conflito ou o sistema aplica regras automáticas.

### [❌] 1.9 Entrega Híbrida: Motoqueiro + Drone Hub

*   **Atores:** Aurora, DroneExpress (Operador do Hub), EcoCliente (ou BistroTech)
*   **Descrição:** Uma entrega é iniciada por um cliente (EcoCliente ou BistroTech), coletada por Aurora, levada até um hub da DroneExpress, e finalizada por um drone, especialmente para entregas em apartamentos onde o acesso pode ser restrito ou indesejado pelo entregador inicial.
*   **Fluxo Principal:**
    1.  EcoCliente (ou BistroTech) solicita uma entrega com destino a um prédio de apartamentos.
    2.  Sistema (Central Node) identifica que o destino é elegível para entrega via drone hub e que Aurora (ou o entregador alocado) tem preferência por não subir em apartamentos.
    3.  Sistema oferece a opção de entrega híbrida (Moto + Drone) ou aloca automaticamente se for a política padrão para o local/entregador.
    4.  Aurora aceita a solicitação, que agora tem como destino intermediário o Hub da DroneExpress mais próximo do destino final.
    5.  Aurora coleta o item no ponto de origem (Restaurante/Loja).
    6.  Aurora utiliza a navegação do app Ionic para ir até o Hub da DroneExpress.
    7.  No Hub, Aurora entrega o pacote ao operador da DroneExpress ou a um ponto de coleta automatizado. Confirma a entrega intermediária no app.
    8.  Sistema (Central Node) notifica a DroneExpress (via API) sobre a chegada do pacote e os detalhes da entrega final.
    9.  DroneExpress prepara e lança um drone com o pacote para o endereço final do EcoCliente.
    10. EcoCliente acompanha a etapa final da entrega (drone) pelo app Ionic (informações via API da DroneExpress -> Central Node -> App).
    11. Drone chega ao local (janela, varanda designada, ou portaria, dependendo da infraestrutura do prédio e regulamentação).
    12. EcoCliente confirma o recebimento (pode envolver código via app).
    13. Sistema (DroneExpress API -> Central Node) marca a entrega como concluída.
    14. Aurora e EcoCliente (e possivelmente DroneExpress) avaliam as respectivas partes da experiência.
*   **Fluxo Alternativo:**
    *   Se o Hub da DroneExpress estiver indisponível (clima, manutenção), o sistema pode re-rotear Aurora para fazer a entrega final diretamente ou buscar outro entregador/modal.
    *   Se o drone não puder completar a entrega (falha técnica, área de pouso obstruída), o operador da DroneExpress é notificado para intervir (contatar cliente, retornar o pacote). Sistema notifica EcoCliente e Aurora (se relevante).
    *   Se a entrega for originada pelo BistroTech, o painel web exibe o status híbrido e as etapas da entrega.

### [❌] 1.10 BistroTech Usa Sistema de Cardápio

*   **Atores:** Cliente, BistroTech
*   **Descrição:** BistroTech usa nosso sistema de cardápio para oferecer produtos. O cliente acessa nosso site, visualiza os produtos, faz pedidos e BistroTech acompanha o status do pedido.
*   **Fluxo Principal:**
    1.  Cliente acessa o site todoke.com.br/bistrotech
    2.  Cliente visualiza os produtos disponíveis organizados por categorias
    3.  Cliente seleciona os produtos desejados e adiciona ao carrinho
    4.  Cliente finaliza o pedido informando dados de entrega e pagamento
    5.  Sistema registra o pedido com status "Em Análise"
    6.  BistroTech recebe alerta no dashboard sobre novo pedido
    7.  BistroTech analisa e muda status para "Aceito"
    8.  BistroTech prepara os produtos e muda status para "Em Preparação"
    9.  Quando pronto, BistroTech muda status para "Aguardando Entregador"
    10. Entregador é alocado e muda status para "Entregador Retirou Produto"
    11. Entregador realiza entrega e muda status para "Entregue"
    12. Cliente recebe notificação de cada mudança de status
*   **Fluxo Alternativo:**
    *   Se BistroTech rejeitar o pedido, status muda para "Cancelado" e cliente é notificado
    *   Se cliente cancelar antes da preparação, status muda para "Cancelado"
    *   Se entregador não for encontrado, status fica "Aguardando Entregador" até alocação
