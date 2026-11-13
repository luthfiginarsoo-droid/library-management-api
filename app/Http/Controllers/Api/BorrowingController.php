<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $borrowings = Borrowing::with(['user:id,name,email', 'book:id,title,isbn'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10);

        return $this->successResponse($borrowings, 'Borrowing list retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id'  => 'required|exists:books,id',
            'due_date' => 'required|date|after:today',
        ]);

        $book = Book::lockForUpdate()->find($validated['book_id']);

        if ($book->stock <= 0) {
            return $this->errorResponse('Book is out of stock', 422);
        }

        DB::beginTransaction();
        try {
            $borrowing = Borrowing::create([
                'user_id'     => auth()->id(),
                'book_id'     => $book->id,
                'borrowed_at' => now()->toDateString(),
                'due_date'    => $validated['due_date'],
                'status'      => 'borrowed',
            ]);

            $book->decrement('stock');

            DB::commit();
            return $this->successResponse($borrowing->load(['user:id,name,email', 'book:id,title']), 'Book borrowed successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to process borrowing', 500);
        }
    }

    public function returnBook(Borrowing $borrowing): JsonResponse
    {
        if ($borrowing->status === 'returned') {
            return $this->errorResponse('Book has already been returned', 422);
        }

        DB::beginTransaction();
        try {
            $borrowing->update([
                'returned_at' => now()->toDateString(),
                'status'      => 'returned',
            ]);

            $borrowing->book()->increment('stock');

            DB::commit();
            return $this->successResponse($borrowing->load(['user:id,name,email', 'book:id,title']), 'Book returned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to process return', 500);
        }
    }
}