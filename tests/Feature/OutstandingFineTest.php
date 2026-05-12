<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OutstandingFineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('transactions');
        Schema::dropIfExists('books');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique();
            $table->string('title');
            $table->string('author');
            $table->integer('copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->decimal('fine_cap', 8, 2)->default(500.00);
            $table->boolean('is_circulating')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type');
            $table->text('details');
            $table->string('status')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users');
            $table->foreignId('book_id')->constrained('books');
            $table->string('action');
            $table->string('status')->default('active');
            $table->date('issued_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('returned_date')->nullable();
            $table->float('fine')->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->string('payment_method', 20)->nullable();
            $table->string('paymongo_reference', 100)->nullable();
            $table->string('receipt_no')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->integer('renewal_count')->default(0);
            $table->integer('max_renewals')->default(0);
            $table->timestamps();
        });
    }

    public function test_rejected_overdue_transactions_are_not_included_in_outstanding_fine()
    {
        $user = User::create([
            'name' => 'Geoff Patrick',
            'email' => 'geoff@example.com',
            'password' => 'secret',
            'role' => 'user',
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'title' => 'Test Book',
            'author' => 'Test Author',
            'copies' => 1,
            'available_copies' => 1,
            'fine_cap' => 500.00,
            'is_circulating' => true,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'checkout',
            'status' => 'overdue',
            'issued_date' => Carbon::today()->subDays(7),
            'due_date' => Carbon::today()->subDays(3),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'reject',
            'status' => 'rejected',
            'issued_date' => Carbon::today()->subDays(5),
            'due_date' => Carbon::today()->subDays(2),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        $this->assertSame(75.00, $user->fresh()->outstanding_fine);

        $pendingFines = Transaction::where('member_id', $user->id)
            ->where('fine_paid', false)
            ->where(function ($query) {
                $query->where('fine', '>', 0)
                      ->orWhere(function ($q) {
                          $q->whereNull('returned_date')
                            ->whereNotNull('due_date')
                            ->whereIn('status', ['active', 'overdue'])
                            ->whereDate('due_date', '<', today());
                      });
            })
            ->get();

        $this->assertCount(1, $pendingFines);
        $this->assertSame(75.00, $pendingFines->sum(fn ($txn) => $txn->outstanding_fine));
    }

    public function test_portal_fines_page_displays_the_correct_total_and_omits_rejected_fines()
    {
        $user = User::create([
            'name' => 'Geoff Patrick',
            'email' => 'geoff@example.com',
            'password' => 'secret',
            'role' => 'user',
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'title' => 'Test Book',
            'author' => 'Test Author',
            'copies' => 1,
            'available_copies' => 1,
            'fine_cap' => 500.00,
            'is_circulating' => true,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'checkout',
            'status' => 'overdue',
            'issued_date' => Carbon::today()->subDays(7),
            'due_date' => Carbon::today()->subDays(3),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'reject',
            'status' => 'rejected',
            'issued_date' => Carbon::today()->subDays(5),
            'due_date' => Carbon::today()->subDays(2),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('portal.fines'));

        $response->assertStatus(200);
        $response->assertSee('₱75.00');
        $response->assertDontSee('₱125.00');
    }

    public function test_portal_transactions_summary_card_shows_the_correct_outstanding_fines()
    {
        $user = User::create([
            'name' => 'Geoff Patrick',
            'email' => 'geoff@example.com',
            'password' => 'secret',
            'role' => 'user',
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'title' => 'Test Book',
            'author' => 'Test Author',
            'copies' => 1,
            'available_copies' => 1,
            'fine_cap' => 500.00,
            'is_circulating' => true,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'checkout',
            'status' => 'overdue',
            'issued_date' => Carbon::today()->subDays(7),
            'due_date' => Carbon::today()->subDays(3),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'reject',
            'status' => 'rejected',
            'issued_date' => Carbon::today()->subDays(5),
            'due_date' => Carbon::today()->subDays(2),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('portal.transactions'));

        $response->assertStatus(200);
        $response->assertSee('Outstanding Fines');
        $response->assertSee('₱75.00');
        $response->assertDontSee('₱125.00');
    }

    public function test_cash_payment_records_the_payment_immediately()
    {
        $user = User::create([
            'name' => 'Geoff Patrick',
            'email' => 'geoff@example.com',
            'password' => 'secret',
            'role' => 'user',
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'title' => 'Test Book',
            'author' => 'Test Author',
            'copies' => 1,
            'available_copies' => 1,
            'fine_cap' => 500.00,
            'is_circulating' => true,
        ]);

        $txn = Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'checkout',
            'status' => 'overdue',
            'issued_date' => Carbon::today()->subDays(7),
            'due_date' => Carbon::today()->subDays(3),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        $response = $this->actingAs($user)
            ->post(route('portal.pay-fine', $txn), [
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect(route('portal.fines'));

        $txn->refresh();
        $this->assertTrue($txn->fine_paid);
        $this->assertSame('cash', $txn->payment_method);
    }

    public function test_gcash_payment_creates_a_valid_paymongo_session()
    {
        $user = User::create([
            'name' => 'Geoff Patrick',
            'email' => 'geoff@example.com',
            'password' => 'secret',
            'role' => 'user',
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'title' => 'Test Book',
            'author' => 'Test Author',
            'copies' => 1,
            'available_copies' => 1,
            'fine_cap' => 500.00,
            'is_circulating' => true,
        ]);

        $txn = Transaction::create([
            'member_id' => $user->id,
            'book_id' => $book->id,
            'action' => 'checkout',
            'status' => 'overdue',
            'issued_date' => Carbon::today()->subDays(7),
            'due_date' => Carbon::today()->subDays(3),
            'fine' => 0,
            'fine_paid' => false,
            'renewal_count' => 0,
            'max_renewals' => 0,
        ]);

        $response = $this->actingAs($user)
            ->post(route('portal.pay-fine', $txn), [
                'payment_method' => 'gcash',
                'contact_number' => '09123456789',
            ]);

        $response->assertStatus(302);
        $txn->refresh();

        $this->assertNotNull($txn->paymongo_reference);
        $this->assertSame('gcash', $txn->payment_method);
    }
}
