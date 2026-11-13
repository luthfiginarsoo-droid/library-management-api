public function up(): void
{
    Schema::create('borrowings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('book_id')->constrained()->cascadeOnDelete();
        $table->date('borrowed_at');
        $table->date('due_date');
        $table->date('returned_at')->nullable();
        $table->enum('status', ['borrowed', 'returned'])->default('borrowed');
        $table->timestamps();
    });
}