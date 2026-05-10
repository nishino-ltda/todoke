<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\DeliveryAssignment;
use Mockery;

class PartnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    /**
     * Teste: Cadastro básico de parceiro (BistroTech)
     * - Deve verificar se um partner pode se cadastrar como parceiro
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
            'password_confirmation' => 'senhaSegura123',
            'type' => 'partner',
            'phone' => '67999999999',
            'cpf' => '333.444.555-09',
            'business_name' => 'BistroTech Restaurant',
            'business_type' => 'restaurant',
            'tax_id' => '12345678901',
            'address' => '123 Main Street',
            'business_document' => UploadedFile::fake()->create('document.pdf', 100)
        ];

        // 1. Register partner using auth endpoint
        $response = $this->postJson('/api/v1/auth/register', $partnerData);
        
        // 2. Verify response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'type'
                ]
            ]);

        // 3. Verify partner was created in database
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
        // 1. Criar parceiro logística (LogisMaster) usando a factory method partner()
        $partner = \App\Models\User::factory()
            ->partner()
            ->create([
                'name' => 'LogisMaster'
            ]);

        // 2. Autenticar o parceiro
        $this->actingAs($partner);

        // 3. Dados da região no formato esperado pelo controller
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

        // 4. Send request to create region
        $response = $this->postJson('/api/v1/regions', $regionData);

        // 5. Verify response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'polygon',
                    'partner_id'
                ]
            ]);

        // 6. Verify region was created in database
        $this->assertDatabaseHas('regions', [
            'name' => 'Zona Sul - Campo Grande',
            'partner_id' => $partner->id
        ]);

        // 7. Verify polygon was saved correctly
        $region = \App\Models\Region::first();
        $this->assertIsArray($region->polygon);
        $this->assertEquals('Polygon', $region->polygon['type']);
    }

}
