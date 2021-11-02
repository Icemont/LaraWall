<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServerDataResource;
use App\Models\Server;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ServerData extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $data = Server::where('ip', '=', $request->ip())
                ->with('services')
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'failed', 'data' => null, 'message' => 'Server not found']);
        }

        return response()->json(new ServerDataResource($data));
    }
}
