<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NovaPoshtaService;
use Illuminate\Http\Request;

class NovaPoshtaController extends Controller
{
    protected NovaPoshtaService $np;

    public function __construct(NovaPoshtaService $np)
    {
        $this->np = $np;
    }

    /**
     * 🔍 Пошук населених пунктів за назвою
     */
    public function searchCities(Request $request)
    {
        $query = $request->input('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->np->searchCities($query);

        return response()->json($results);
    }

    /**
     * 🏤 Отримати список відділень або поштоматів
     */
    public function getWarehouses(Request $request)
    {
        $ref = $request->input('ref');
        $type = $request->input('type', 'Warehouse'); // або 'Postomat'

        if (!$ref) {
            return response()->json(['error' => 'Missing city reference'], 400);
        }

        $results = $this->np->getWarehouses($ref, $type);

        return response()->json($results);
    }

    /**
     * 📦 Отримати конкретне відділення по np_ref
     */
    public function getWarehouseByRef(string $ref)
    {
        $warehouse = $this->np->getWarehouseByRef($ref);

        if ($warehouse) {
            return response()->json($warehouse);
        }

        return response()->json([
            'message' => 'Відділення не знайдено',
        ], 404);
    }
}
