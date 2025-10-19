<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['user', 'category']);

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Por defecto solo mostrar publicados si no está autenticado
            if (!auth('api')->check()) {
                $query->where('status', 'published');
            }
        }

        $articles = $query->latest()->paginate(10);

        return response()->json($articles);
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== CREAR ARTÍCULO ===');

            // Verificar autenticación y autorización (Admin)
            if (!auth('api')->check() || !auth()->user()->hasRole('admin')) {
                Log::error('Usuario no autorizado para crear artículo');
                return response()->json(['error' => 'No autorizado'], 403);
            }
            Log::info('Usuario autenticado: ' . auth()->user()->email);

            // Validación
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string',
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'in:draft,published',
                // El campo 'image' es el que espera el archivo File enviado desde Vue
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                Log::error('Validación fallida', $validator->errors()->toArray());
                return response()->json($validator->errors(), 422);
            }

            Log::info('Validación pasada');

            // Preparar datos excluyendo los campos de imagen que se manejan aparte
            $data = $request->only(['title', 'content', 'excerpt', 'category_id', 'status']);
            $data['status'] = $data['status'] ?? 'draft';
            $data['user_id'] = auth()->id();

            // Manejar imagen (Lógica correcta para el disco 'public')
            if ($request->hasFile('image')) {
                Log::info('Procesando imagen');
                // Almacena en storage/app/public/articles
                $imagePath = $request->file('image')->store('articles', 'public');
                $data['image'] = $imagePath;
                Log::info('Imagen guardada en: ' . $imagePath);
            }

            // Crear artículo
            $article = Article::create($data);
            $article->load(['user', 'category']);

            Log::info('Artículo creado exitosamente con ID: ' . $article->id);

            return response()->json($article, 201);

        } catch (\Exception $e) {
            Log::error('Error al crear artículo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Error al crear artículo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $article = Article::with(['user', 'category'])->findOrFail($id);

            // Si no está publicado, solo el autor o admin puede verlo
            if ($article->status !== 'published') {
                if (!auth('api')->check()) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }

                $user = auth()->user();
                if ($user->id !== $article->user_id && !$user->hasRole('admin')) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }
            }

            return response()->json($article);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Artículo no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('=== ACTUALIZAR ARTÍCULO ID: ' . $id . ' ===');

            // Verificar autenticación
            if (!auth('api')->check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $article = Article::findOrFail($id);

            // Verificar permisos
            $user = auth()->user();
            if ($user->id !== $article->user_id && !$user->hasRole('admin')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Validación
            // NOTA: Se eliminan las reglas 'required' ya que el PUT/PATCH puede ser parcial
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255', // Cambiado a nullable
                'excerpt' => 'nullable|string',
                'content' => 'nullable|string', // Cambiado a nullable
                'category_id' => 'exists:categories,id',
                'status' => 'in:draft,published',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                // Se valida el campo 'remove_image' que viene del frontend
                'remove_image' => 'nullable|in:1',
            ]);

            if ($validator->fails()) {
                Log::error('Validación fallida', $validator->errors()->toArray());
                return response()->json($validator->errors(), 422);
            }

            // Preparar datos
            // Excluimos los campos de imagen y el método spoofing
            $data = $request->only(['title', 'excerpt', 'content', 'category_id', 'status']);

            // --- Lógica para MANEJAR LA IMAGEN (FIX CRÍTICO) ---

            // 1. Manejar la solicitud de ELIMINAR la imagen actual (viene del frontend)
            if ($request->has('remove_image') && $article->image) {
                 // Si el frontend envió el flag 'remove_image'
                Log::info('Solicitud de eliminación de imagen detectada.');
                Storage::disk('public')->delete($article->image);
                $data['image'] = null; // Establece el campo de la DB a NULL
                $article->image = null; // Actualiza el objeto Article inmediatamente
                Log::info('Imagen anterior eliminada y campo DB puesto a NULL.');
            }

            // 2. Manejar la solicitud de SUBIR una nueva imagen
            if ($request->hasFile('image')) {
                Log::info('Procesando nueva imagen.');

                // Eliminar la imagen anterior (si no fue eliminada en el paso 1)
                // Esto también cubre el caso donde se sube una imagen para reemplazar una existente,
                // sin necesidad de haber usado el botón "Eliminar".
                if ($article->image) {
                    Storage::disk('public')->delete($article->image);
                    Log::info('Imagen anterior reemplazada y eliminada: ' . $article->image);
                }

                // Guardar nueva imagen
                $imagePath = $request->file('image')->store('articles', 'public');
                $data['image'] = $imagePath; // Añade la nueva ruta a los datos de actualización
                Log::info('Nueva imagen guardada en: ' . $imagePath);
            }
            // NOTA: Si no se sube una nueva imagen y tampoco se pidió eliminar,
            // el campo 'image' NO se incluye en $data, dejando el valor actual intacto.

            // Actualizar artículo (solo los campos presentes en $data)
            $article->update($data);
            $article->load(['user', 'category']);

            Log::info('Artículo actualizado exitosamente');

            return response()->json($article);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Artículo no encontrado'], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar artículo: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al actualizar artículo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verificar autenticación
            if (!auth('api')->check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $article = Article::findOrFail($id);

            // Verificar permisos
            $user = auth()->user();
            if ($user->id !== $article->user_id && !$user->hasRole('admin')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Eliminar imagen si existe
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
                Log::info('Imagen eliminada: ' . $article->image);
            }

            $article->delete();

            Log::info('Artículo eliminado exitosamente ID: ' . $id);

            return response()->json(['message' => 'Artículo eliminado exitosamente']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Artículo no encontrado'], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar artículo: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al eliminar artículo',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
