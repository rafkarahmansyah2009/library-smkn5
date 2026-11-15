<?php

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Fine;
use App\Models\User;
use App\Services\MidtransService;
use Carbon\Carbon;

test('member can view their own fines list', function () {
    $member1 = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $member2 = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing1 = Borrowing::create([
        'transaction_code' => 'PJ-USER1',
        'user_id' => $member1->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(3),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine1 = Fine::create([
        'borrowing_id' => $borrowing1->id,
        'user_id' => $member1->id,
        'amount' => 3000,
        'days_overdue' => 3,
        'status' => 'unpaid',
    ]);

    $borrowing2 = Borrowing::create([
        'transaction_code' => 'PJ-USER2',
        'user_id' => $member2->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine2 = Fine::create([
        'borrowing_id' => $borrowing2->id,
        'user_id' => $member2->id,
        'amount' => 2000,
        'days_overdue' => 2,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($member1)->get(route('member.fines.index'));

    $response->assertOk()
        ->assertSee('PJ-USER1')
        ->assertSee('Rp 3.000')
        ->assertDontSee('PJ-USER2');
});

test('member can request snap token for their own unpaid fine', function () {
    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-SNAP01',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(3),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 5000,
        'days_overdue' => 5,
        'status' => 'unpaid',
    ]);

    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('createSnapToken')
            ->once()
            ->andReturn('dummy-snap-token-abc-123');
    });

    $response = $this->actingAs($member)->postJson(route('member.fines.pay', $fine));

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'snap_token' => 'dummy-snap-token-abc-123',
        ]);
});

test('member cannot request snap token for fine belonging to other user', function () {
    $member1 = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $member2 = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-SNAP02',
        'user_id' => $member2->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member2->id,
        'amount' => 2000,
        'days_overdue' => 2,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($member1)->postJson(route('member.fines.pay', $fine));

    $response->assertForbidden();
});

test('member cannot request snap token for already paid fine', function () {
    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-SNAP03',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 2000,
        'days_overdue' => 2,
        'status' => 'paid',
        'payment_date' => Carbon::now(),
        'paid_at' => Carbon::now(),
        'payment_method' => 'tunai',
    ]);

    $response = $this->actingAs($member)->postJson(route('member.fines.pay', $fine));

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Denda ini sudah lunas.',
        ]);
});

test('midtrans webhook rejects request with invalid signature', function () {
    config(['midtrans.server_key' => 'secret-test-server-key']);

    $response = $this->postJson(route('midtrans.notification'), [
        'order_id' => 'DENDA-1-123456',
        'status_code' => '200',
        'gross_amount' => '10000.00',
        'signature_key' => 'invalid-signature-key',
        'transaction_status' => 'settlement',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid signature key']);
});

test('midtrans webhook successfully marks fine as paid on settlement status', function () {
    $serverKey = 'secret-test-server-key';
    config(['midtrans.server_key' => $serverKey]);

    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-WEBHOOK01',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $orderId = 'DENDA-TEST-999';
    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 10000,
        'days_overdue' => 10,
        'status' => 'unpaid',
        'midtrans_order_id' => $orderId,
        'snap_token' => 'dummy-token-123',
    ]);

    $statusCode = '200';
    $grossAmount = '10000.00';
    $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson(route('midtrans.notification'), [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $validSignature,
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'success']);

    $fine->refresh();
    expect($fine->status)->toBe('paid')
        ->and($fine->payment_method)->toBe('midtrans')
        ->and($fine->paid_at)->not->toBeNull()
        ->and($fine->payment_date)->not->toBeNull();
});

test('midtrans webhook is idempotent when duplicate settlement notification arrives', function () {
    $serverKey = 'secret-test-server-key';
    config(['midtrans.server_key' => $serverKey]);

    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-IDEM01',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $orderId = 'DENDA-IDEM-001';
    $initialPaidAt = Carbon::now()->subMinutes(5);

    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 10000,
        'days_overdue' => 10,
        'status' => 'paid',
        'payment_method' => 'midtrans',
        'midtrans_order_id' => $orderId,
        'paid_at' => $initialPaidAt,
        'payment_date' => $initialPaidAt,
    ]);

    $statusCode = '200';
    $grossAmount = '10000.00';
    $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson(route('midtrans.notification'), [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $validSignature,
        'transaction_status' => 'settlement',
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'success', 'message' => 'Fine is already marked as paid.']);

    $fine->refresh();
    expect($fine->status)->toBe('paid')
        ->and($fine->payment_method)->toBe('midtrans');
});

test('midtrans webhook clears snap token on deny, cancel, or expire', function () {
    $serverKey = 'secret-test-server-key';
    config(['midtrans.server_key' => $serverKey]);

    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-CANCEL01',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(2),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $orderId = 'DENDA-CANCEL-001';
    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 4000,
        'days_overdue' => 4,
        'status' => 'unpaid',
        'midtrans_order_id' => $orderId,
        'snap_token' => 'active-token-to-be-cleared',
    ]);

    $statusCode = '200';
    $grossAmount = '4000.00';
    $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson(route('midtrans.notification'), [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $validSignature,
        'transaction_status' => 'expire',
    ]);

    $response->assertOk();

    $fine->refresh();
    expect($fine->status)->toBe('unpaid')
        ->and($fine->snap_token)->toBeNull();
});

test('admin fine modal displays formatted amount and cash payment sets payment_method to tunai', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    $category = Category::factory()->create();
    $book = Book::factory()->create(['category_id' => $category->id]);

    $borrowing = Borrowing::create([
        'transaction_code' => 'PJ-ADMINPAY',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => Carbon::today()->subDays(10),
        'due_date' => Carbon::today()->subDays(5),
        'return_date' => Carbon::today(),
        'status' => 'returned',
    ]);

    $fine = Fine::create([
        'borrowing_id' => $borrowing->id,
        'user_id' => $member->id,
        'amount' => 5000,
        'days_overdue' => 5,
        'status' => 'unpaid',
    ]);

    // Check fine accessor
    expect($fine->formatted_amount)->toBe('Rp 5.000');

    // Admin view index renders fine amount in modal and table
    $viewResponse = $this->actingAs($admin)->get(route('admin.fines.index'));
    $viewResponse->assertOk()
        ->assertSee('Rp 5.000')
        ->assertSee('Konfirmasi Pelunasan Denda');

    // Admin pays cash
    $payResponse = $this->actingAs($admin)->post(route('admin.fines.pay', $fine), [
        'notes' => 'Pembayaran tunai di meja perpustakaan',
    ]);

    $payResponse->assertRedirect(route('admin.fines.index'));

    $fine->refresh();
    expect($fine->status)->toBe('paid')
        ->and($fine->payment_method)->toBe('tunai')
        ->and($fine->paid_at)->not->toBeNull()
        ->and($fine->payment_date)->not->toBeNull()
        ->and($fine->paid_to_user_id)->toBe($admin->id);
});
