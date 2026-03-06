<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessoResource extends JsonResource
{
    public function toArray($request): array
{
    return [
        'id'      => $this->id,
        'numero'  => $this->numero_processo,
        'titulo'  => $this->titulo,
        'status'  => $this->status,
        'cliente' => $this->cliente->nome ?? 'Não informado',
        // O tenant_id NÃO entra aqui, por isso ele deixará de aparecer
    ];
}
}
