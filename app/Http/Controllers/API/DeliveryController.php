<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origem.lat' => 'required|numeric|between:-90,90',
            'origem.lng' => 'required|numeric|between:-180,180',
            'origem.endereco' => 'required|string|max:255',
            'destino.lat' => 'required|numeric|between:-90,90',
            'destino.lng' => 'required|numeric|between:-180,180',
            'destino.endereco' => 'required|string|max:255',
            'descricaoItem' => 'required|string|max:255',
            'pesoEstimado' => 'required|numeric|min:0|max:999.99',
            'dimensoes.largura' => 'required|integer|min:1|max:999',
            'dimensoes.altura' => 'required|integer|min:1|max:999',
            'dimensoes.profundidade' => 'required|integer|min:1|max:999',
            'tipo' => 'required|in:normal,expressa,sustentavel'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $valor = $this->calculateDeliveryValue($request);
        $tempoEstimado = $this->calculateEstimatedTime($request);

        $delivery = Delivery::create([
            'clienteId' => $request->user()->id,
            'origem' => $request->origem,
            'destino' => $request->destino,
            'descricaoItem' => $request->descricaoItem,
            'pesoEstimado' => $request->pesoEstimado,
            'dimensoes' => $request->dimensoes,
            'tipo' => $request->tipo,
            'status' => 'pendente',
            'valor' => $valor,
            'tempoEstimado' => $tempoEstimado,
            'codigoConfirmacao' => substr(md5(uniqid()), 0, 6)
        ]);

        return response()->json([
            'id' => $delivery->id,
            'valor' => $valor,
            'tempoEstimado' => $tempoEstimado,
            'codigoConfirmacao' => $delivery->codigoConfirmacao
        ], 201);
    }

    public function accept(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status !== 'pendente') {
            return response()->json([
                'message' => 'Esta entrega já foi aceita'
            ], 409);
        }

        $delivery->update([
            'entregadorId' => $request->user()->id,
            'status' => 'aceito'
        ]);

        return response()->json($delivery, 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['coletado', 'em_transito', 'entregue'];
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
            'codigoConfirmacao' => 'sometimes|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $delivery = Delivery::findOrFail($id);
        $delivery->update(['status' => $request->status]);

        return response()->json($delivery, 200);
    }

    private function calculateDeliveryValue(Request $request): float
    {
        // Lógica simplificada para cálculo do valor
        $baseValue = 10.0;
        $distanceFactor = 0.5;
        $weightFactor = $request->pesoEstimado * 0.1;
        $typeFactor = match($request->tipo) {
            'expressa' => 1.5,
            'sustentavel' => 1.2,
            default => 1.0
        };

        return round($baseValue + $distanceFactor + $weightFactor * $typeFactor, 2);
    }

    private function calculateEstimatedTime(Request $request): int
    {
        // Lógica simplificada para cálculo do tempo estimado (em minutos)
        return match($request->tipo) {
            'expressa' => 30,
            'sustentavel' => 90,
            default => 60
        };
    }
}
