// Field name mappings from Portuguese to English
export const fieldMappings = {
  // Users table
  'telefone': 'phone',
  'fotoUrl': 'photoUrl',
  'tipo': 'type',
  'cliente': 'client', // enum value

  // Products table
  'restaurantId': 'restaurant_id',
  'imagemUrl': 'imageUrl',
  'disponivel': 'available',
  'indisponivel': 'unavailable',

  // Orders table
  'clientId': 'client_id',
  'restaurantId': 'restaurant_id',
  'totalValue': 'total_value',
  'deliveryId': 'delivery_id',

  // Order items table
  'unitPrice': 'unit_price',

  // Deliveries table
  'clienteId': 'client_id',
  'entregadorId': 'courrier_id',
  'posicaoAtual': 'current_position',
  'historicoStatus': 'status_history',
  'pendente': 'pending',
  'aceito': 'accepted',
  'em_transporte': 'in_transit',
  'entregue': 'delivered',
  'cancelado': 'canceled',
  'tipo': 'type',
  'descricaoItem': 'item_description',
  'pesoEstimado': 'estimated_weight',
  'dimensoes': 'dimensions',
  'valor': 'value',
  'tempoEstimado': 'estimated_time',
  'codigoConfirmacao': 'confirmation_code',
  'nodeId': 'node_id',

  // Nodes table
  'parceiroId': 'partner_id',
  'tipo': 'type',
  'capacidade': 'capacity',
  'ativo': 'active',
  'inativo': 'inactive',
  'manutencao': 'maintenance',
  'regiaoId': 'region_id',

  // Regions table
  'poligono': 'polygon',

  // Ratings table
  'entregaId': 'delivery_id',
  'avaliadorId': 'rater_id',
  'avaliadoId': 'rated_id',
  'nota': 'rating',
  'comentario': 'comment',

  // Messages table
  'texto': 'text',

  // Delivery assignments table
  'etapa': 'stage'
};
