<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index()
    {
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();
        return view('pages.api-keys', compact('apiKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $key = 'api_' . Str::random(32);

        $apiKey = ApiKey::create([
            'name' => $request->name,
            'key' => $key,
            'active' => true,
        ]);

        return response()->json([
            'message' => 'API Key generated successfully',
            'apiKey' => $apiKey
        ]);
    }

    public function update(Request $request, $id)
    {
        $apiKey = ApiKey::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $apiKey->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'API Key updated successfully',
            'apiKey' => $apiKey
        ]);
    }

    public function toggleActive($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->update([
            'active' => !$apiKey->active,
        ]);

        return response()->json([
            'message' => 'API Key status updated successfully',
            'apiKey' => $apiKey
        ]);
    }

    public function destroy($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return response()->json([
            'message' => 'API Key deleted successfully',
        ]);
    }

    public function regenerate($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $newKey = 'api_' . Str::random(32);

        $apiKey->update([
            'key' => $newKey,
            'last_used_at' => now(), // Simulating usage or just reset
        ]);

        return response()->json([
            'message' => 'API Key regenerated successfully',
            'apiKey' => $apiKey
        ]);
    }
}
