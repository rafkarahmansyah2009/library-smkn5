<?php

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\User;

test('admin can create member with avatar url', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.members.store'), [
        'name' => 'Siswa Baru',
        'email' => 'siswabaru@example.com',
        'username' => 'siswabaru',
        'member_type' => 'siswa',
        'nis' => '12345678',
        'class' => 'X RPL 1',
        'major' => 'RPL',
        'phone' => '081234567890',
        'password' => 'password123',
        'avatar_url' => 'https://example.com/siswa-avatar.jpg',
    ]);

    $response->assertRedirect(route('admin.members.index'));

    $member = User::where('email', 'siswabaru@example.com')->first();
    expect($member)->not->toBeNull();
    expect($member->avatar)->toBe('https://example.com/siswa-avatar.jpg');
});

test('admin can update member avatar url', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
        'username' => 'siswa87',
        'member_type' => 'siswa',
        'nis' => '87654321',
        'status' => 'active',
        'avatar' => 'https://example.com/old-avatar.jpg',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.members.update', $member), [
        'name' => $member->name,
        'email' => $member->email,
        'username' => $member->username,
        'member_type' => 'siswa',
        'nis' => $member->nis,
        'status' => 'active',
        'avatar_url' => 'https://example.com/new-avatar.jpg',
    ]);

    $response->assertRedirect(route('admin.members.index'));

    expect($member->fresh()->avatar)->toBe('https://example.com/new-avatar.jpg');
});

test('admin can soft delete a member', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.members.destroy', $member));

    $response->assertRedirect(route('admin.members.index'));
    expect($member->fresh()->deleted_at)->not->toBeNull();
    expect(User::where('role', 'member')->count())->toBe(0);
    expect(User::withTrashed()->where('id', $member->id)->exists())->toBeTrue();
});

test('admin can restore a soft deleted member', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
        'status' => 'active',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.members.restore', $member->id));

    $response->assertRedirect(route('admin.members.index'));
    expect($member->fresh()->deleted_at)->toBeNull();
    expect(User::where('role', 'member')->count())->toBe(1);
});

test('admin can force delete a soft deleted member', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
        'status' => 'active',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.members.force-delete', $member->id));

    $response->assertRedirect(route('admin.members.index'));
    expect(User::withTrashed()->where('id', $member->id)->exists())->toBeFalse();
});

test('admin cannot soft delete member with active borrowing', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $member = User::factory()->create([
        'role' => 'member',
        'status' => 'active',
    ]);

    $category = Category::factory()->create();
    $book = Book::factory()->create([
        'category_id' => $category->id,
    ]);

    Borrowing::create([
        'transaction_code' => 'TRX-TEST-001',
        'user_id' => $member->id,
        'book_id' => $book->id,
        'borrow_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'borrowed',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.members.destroy', $member));

    $response->assertSessionHas('error');
    expect($member->fresh()->deleted_at)->toBeNull();
});
