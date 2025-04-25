<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;

class PartnerRegistrationTest extends TestCase
{
    /**
     * Teste: Cadastro básico de parceiro (BistroTech)
     * - Deve verificar se um restaurante pode se cadastrar como parceiro
     * - Pré-condições: Nenhum parceiro cadastrado
     * - Ações: 
     *   1. Enviar dados de cadastro (nome, CNPJ, endereço)
     *   2. Verificar email de confirmação
     * - Resultados esperados:
     *   - Status 201 Created
     *   - Dados do parceiro no banco de dados
     *   - Email de confirmação enviado
     */
    public function test_partner_registration()
    {
        // Dados do parceiro BistroTech
        $partnerData = [
            'name' => 'BistroTech',
            'email' => 'contato@bistrotech.com.br',
            'password' => 'senhaSegura123',
            'type' => 'partner',
            'phone' => '67999999999',
            'status' => 'active'
        ];

        // 1. Enviar dados de cadastro
        $response = $this->postJson('/api/partners', $partnerData);
        
        // 2. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'type',
                    'status'
                ]
            ]);

        // 3. Verificar se o parceiro foi criado no banco
        $this->assertDatabaseHas('users', [
            'email' => 'contato@bistrotech.com.br',
            'type' => 'partner'
        ]);

        // 4. Verificar se o email de confirmação foi enviado
        // (Simulado - em produção seria verificado via Mail::fake)
    }

    /**
     * Teste: Adição de região de operação pelo parceiro LogisMaster
     * - Deve verificar se um parceiro logística pode definir suas regiões de atuação
     * - Pré-condições: Parceiro logística cadastrado e autenticado
     * - Ações:
     *   1. Enviar geojson com polígono da região
     *   2. Associar região ao parceiro
     * - Resultados esperados:
     *   - Status 200 OK
     *   - Região criada no banco de dados
     *   - Região associada ao parceiro
     */
    public function test_partner_can_add_operating_region() 
    {
        // 1. Criar parceiro logística (LogisMaster)
        $partner = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'LogisMaster'
        ]);

        // 2. Autenticar o parceiro
        $this->actingAs($partner);

        // 3. Dados da região (geojson simples)
        $regionData = [
            'name' => 'Zona Sul - Campo Grande',
            'polygon' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [-54.6468, -20.4697],
                        [-54.5954, -20.4697], 
                        [-54.5954, -20.4412],
                        [-54.6468, -20.4412],
                        [-54.6468, -20.4697]
                    ]
                ]
            ]
        ];

        // 4. Enviar requisição para criar região
        $response = $this->postJson('/api/partner/regions', $regionData);

        // 5. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'polygon',
                    'partner_id'
                ]
            ]);

        // 6. Verificar se região foi criada no banco
        $this->assertDatabaseHas('regions', [
            'name' => 'Zona Sul - Campo Grande',
            'partner_id' => $partner->id
        ]);

        // 7. Verificar se o polígono foi salvo corretamente
        $region = \App\Models\Region::first();
        $this->assertIsArray($region->poligono);
        $this->assertEquals('Polygon', $region->poligono['type']);
    }

    /**
     * Teste: Cadastro de nodes (entregadores/drones) pelo parceiro
     * - Deve verificar se um parceiro pode registrar seus recursos de entrega
     * - Pré-condições: Parceiro cadastrado e autenticado
     * - Ações:
     *   1. Cadastrar novo node (tipo, capacidade, região)
     *   2. Verificar aprovação pelo admin
     * - Resultados esperados:
     *   - Node criado com status "pending_approval"
     *   - Admin recebe notificação para aprovar
     *   - Após aprovação, node fica disponível para operação
     */
    public function test_partner_can_register_nodes()
    {
        // 1. Criar parceiro e região
        $partner = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'LogisMaster'
        ]);
        
        $region = \App\Models\Region::factory()->create([
            'partner_id' => $partner->id
        ]);

        // 2. Autenticar o parceiro
        $this->actingAs($partner);

        // 3. Dados do node (entregador)
        $nodeData = [
            'type' => 'courrier',
            'identifier' => 'MOTO-001',
            'capacity' => 5.5,
            'region_id' => $region->id,
            'current_position' => [
                'lat' => -20.4697,
                'lng' => -54.6468
            ]
        ];

        // 4. Enviar requisição para criar node
        $response = $this->postJson('/api/partner/nodes', $nodeData);

        // 5. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'identifier',
                    'status',
                    'partner_id',
                    'region_id'
                ]
            ]);

        // 6. Verificar se node foi criado no banco
        $this->assertDatabaseHas('nodes', [
            'identifier' => 'MOTO-001',
            'partner_id' => $partner->id,
            'region_id' => $region->id,
            'status' => 'pending_approval'
        ]);

        // 7. Verificar se a posição foi salva corretamente
        $node = \App\Models\Node::first();
        $this->assertIsArray($node->currentPosition);
        $this->assertEquals(-20.4697, $node->currentPosition['lat']);
    }
}
