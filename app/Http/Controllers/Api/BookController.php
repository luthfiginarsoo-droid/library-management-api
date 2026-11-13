<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $books = Book::with('category:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
            })
            ->when($request->category_id, fn($q, $catId) => $q->where('category_id', $catId))
            ->latest()
            ->paginate(10);

        return $this->successResponse($books, 'Books retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'isbn'        => 'required|string|max:20|unique:books,isbn',
            'stock'       => 'required|integer|min:0',
        ]);

        $book = Book::create($validated)->load('category:id,name');
        return $this->successResponse($book, 'Book created successfully', 201);
    }

    public function show(Book $book): JsonResponse
    {
        return $this->successResponse($book->load('category:id,name'), 'Book detail retrieved');
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title'       => 'sometimes|required|string|max:255',
            'author'      => 'sometimes|required|string|max:255',
            'isbn'        => 'sometimes|required|string|max:20|unique:books,isbn,' . $book->id,
            'stock'       => 'sometimes|required|integer|min:0',
        ]);

        $book->update($validated);
        return $this->successResponse($book->load('category:id,name'), 'Book updated successfully');
    }

    public function destroy(Book $book): JsonResponse
    {
        $hasActiveBorrowing = $book->borrowings()->where('status', 'borrowed')->exists();
        if ($hasActiveBorrowing) {
            return $this->errorResponse('Cannot delete book that is currently being borrowed', 422);
        }

        $book->delete();
        return $this->successResponse(null, 'Book deleted successfully');
    }
}