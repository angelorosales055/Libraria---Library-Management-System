<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── STAFF ACCOUNTS ──────────────────────────────────────────
        $admin = User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@libraria.edu',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Rosa Dela Vega',
            'email'     => 'librarian@libraria.edu',
            'password'  => Hash::make('password'),
            'role'      => 'librarian',
            'is_active' => true,
        ]);

        // ── CATEGORIES ──────────────────────────────────────────────
        $cats = [
            'Fiction'     => 'Novels, short stories, and imaginative literature',
            'Technology'  => 'Computers, programming, and digital innovation',
            'History'     => 'Historical events, biographies, and civilizations',
            'Business'    => 'Entrepreneurship, management, and economics',
            'Philosophy'  => 'Ideas, thinking, and existential questions',
            'Arts'        => 'Visual arts, music, and creative expression',
            'Biology'     => 'Life sciences, organisms, and ecosystems',
            'Science'     => 'Physics, chemistry, and scientific discoveries',
        ];
        $catModels = [];
        foreach ($cats as $name => $desc) {
            $catModels[$name] = Category::create(['name' => $name, 'description' => $desc]);
        }

        // ── BOOKS ────────────────────────────────────────────────────
        $books = [
            ['Noli Me Tangere',          'Jose Rizal',          'Fiction',  6,  'Fiction – Row A1', '978-971-8510-05-8'],
            ['El Filibusterismo',         'Jose Rizal',          'Fiction',  4,  'Fiction – Row A1', '978-971-8510-06-5'],
            ['Florante at Laura',         'Francisco Balagtas',  'Fiction',  3,  'Fiction – Row A2', '978-971-0101-01-1'],
            ['Noli Me Tangere (English)', 'Jose Rizal',          'Fiction',  2,  'Fiction – Row A1', '978-0-14-043903-2'],
            ['Harry Potter Vol. 1',       'J.K. Rowling',        'Fiction',  5,  'Fiction – Row B1', '978-0-439-70818-8'],
            ['To Kill a Mockingbird',     'Harper Lee',          'Fiction',  4,  'Fiction – Row B3', '978-0-06-112008-4'],
            ['Brief History of Time',     'S. Hawking',          'Science',  3,  'Science – Row C1', '978-0-553-38016-3'],
            ['Sapiens',                   'Yuval Noah Harari',   'History',  5,  'History – Row D2', '978-0-06-231609-7'],
            ['Homo Deus',                 'Yuval Noah Harari',   'History',  3,  'History – Row D2', '978-0-06-246431-6'],
            ['The Alchemist',             'Paulo Coelho',        'Fiction',  7,  'Fiction – Row B2', '978-0-06-112241-5'],
            ['Clean Code',                'Robert C. Martin',    'Technology', 4,'Tech – Row E1',    '978-0-13-235088-4'],
            ['The Pragmatic Programmer',  'D. Thomas & A. Hunt', 'Technology', 3,'Tech – Row E1',    '978-0-13-595705-9'],
            ['Rich Dad Poor Dad',         'Robert T. Kiyosaki',  'Business', 6,  'Business – Row F1','978-1-61268-120-2'],
            ['Thinking Fast and Slow',    'Daniel Kahneman',     'Philosophy', 3,'Phil – Row G1',    '978-0-374-53355-7'],
            ['The Republic',              'Plato',               'Philosophy', 2,'Phil – Row G2',    '978-0-14-044914-7'],
        ];

        $bookModels = [];
        foreach ($books as [$title, $author, $cat, $copies, $shelf, $isbn]) {
            $bookModels[] = Book::create([
                'title'            => $title,
                'author'           => $author,
                'category_id'      => $catModels[$cat]->id,
                'copies'           => $copies,
                'available_copies' => $copies,
                'shelf'            => $shelf,
                'isbn'             => $isbn,
                'accession_no'     => 'ACC-2025-'.str_pad(count($bookModels)+1, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // ── MEMBERS ─────────────────────────────────────────────────
        $members = [
            ['Maria Santos',    'maria@example.com',   'MBR-2024-0001', 'student'],
            ['Juan dela Cruz',  'juan@example.com',    'MBR-2024-0002', 'faculty'],
            ['Ana Reyes',       'ana@example.com',     'MBR-2024-0003', 'student'],
            ['Carlos Bautista', 'carlos@example.com',  'MBR-2024-0004', 'student'],
            ['Ben Lim',         'ben@example.com',     'MBR-2024-0005', 'public'],
        ];
        $memberModels = [];
        foreach ($members as [$name, $email, $mid, $type]) {
            $memberModels[] = User::create([
                'name'      => $name,
                'email'     => $email,
                'password'  => Hash::make('password'),
                'role'      => 'user',
                'member_id' => $mid,
                'type'      => $type,
                'is_active' => true,
            ]);
        }

        // ── SAMPLE TRANSACTIONS ──────────────────────────────────────
        // Active checkout
        $txn1 = Transaction::create([
            'member_id'   => $memberModels[0]->id,
            'book_id'     => $bookModels[0]->id,
            'issued_by'   => $admin->id,
            'action'      => 'checkout',
            'status'      => 'active',
            'issued_date' => now()->subDays(5),
            'due_date'    => now()->addDays(9),
            'fine'        => 0,
            'fine_paid'   => false,
        ]);
        $bookModels[0]->decrement('available_copies');

        // Returned transaction
        Transaction::create([
            'member_id'     => $memberModels[1]->id,
            'book_id'       => $bookModels[4]->id,
            'issued_by'     => $admin->id,
            'action'        => 'return',
            'status'        => 'returned',
            'issued_date'   => now()->subDays(20),
            'due_date'      => now()->subDays(6),
            'returned_date' => now()->subDays(2),
            'fine'          => 0,
            'fine_paid'     => true,
        ]);

        // Overdue transaction
        $txnOverdue = Transaction::create([
            'member_id'   => $memberModels[2]->id,
            'book_id'     => $bookModels[6]->id,
            'issued_by'   => $admin->id,
            'action'      => 'checkout',
            'status'      => 'overdue',
            'issued_date' => now()->subDays(18),
            'due_date'    => now()->subDays(4),
            'fine'        => 4 * Transaction::FINE_PER_DAY,
            'fine_paid'   => false,
        ]);
        $bookModels[6]->decrement('available_copies');

        // Another active
        Transaction::create([
            'member_id'   => $memberModels[3]->id,
            'book_id'     => $bookModels[9]->id,
            'issued_by'   => $admin->id,
            'action'      => 'checkout',
            'status'      => 'active',
            'issued_date' => now()->subDays(3),
            'due_date'    => now()->addDays(11),
            'fine'        => 0,
            'fine_paid'   => false,
        ]);
        $bookModels[9]->decrement('available_copies');

        $this->command->info('✅ Libraria seeded successfully!');
        $this->command->line('   Admin:     admin@libraria.edu     / password');
        $this->command->line('   Librarian: librarian@libraria.edu / password');
        $this->command->line('   Members:   maria@example.com etc. / password');
    }
}
