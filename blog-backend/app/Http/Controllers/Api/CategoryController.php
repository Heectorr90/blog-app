<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CategoryController extends Controller
{
    // ELIMINAR TODO EL CONSTRUCTOR
    // NO USAR: public function __construct() { ... }

    public function index()
    {
        try {
            $categories = Category::withCount('articles')->get();
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener categorías',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Verificar autenticación y rol
        if (!auth('api')->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = Category::create($request->all());

        return response()->json($category, 201);
    }

    public function show($id)
    {
        try {
            $category = Category::withCount('articles')->findOrFail($id);
            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        // Verificar autenticación y rol
        if (!auth('api')->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:categories,name,'.$id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category->update($request->all());

        return response()->json($category);
    }

    public function destroy($id)
    {
        // Verificar autenticación y rol
        if (!auth('api')->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $category = Category::findOrFail($id);

        // Verificar que no tenga artículos
        if ($category->articles()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar una categoría con artículos asociados'
            ], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada exitosamente']);
    }
}
