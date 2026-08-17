<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\User;
use App\Services\CashRegisterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

class CashRegisterServiceTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    protected function prepareDatabase(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->nullable();
                $table->foreignId('branch_id')->nullable();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('branch_id')->nullable();
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->nullable();
                $table->decimal('expected_balance', 15, 2)->nullable();
                $table->decimal('difference', 15, 2)->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cash_movements')) {
            Schema::create('cash_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_register_id');
                $table->foreignId('user_id')->nullable();
                $table->enum('type', ['in', 'out']);
                $table->decimal('amount', 15, 2);
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('cash_register_id')->nullable();
                $table->foreignId('user_id');
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->enum('status', ['held', 'completed', 'cancelled', 'refunded'])->default('completed');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id');
                $table->enum('method', ['cash', 'qris', 'debit', 'credit', 'e_wallet', 'bank_transfer']);
                $table->decimal('amount', 15, 2);
                $table->timestamps();
            });
        }
    }

    public function test_it_rejects_a_second_open_shift_for_the_same_user(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CU-001',
        ]);

        $user = User::create([
            'branch_id' => $branch->id,
            'name' => 'Kasir Satu',
            'email' => 'kasir1@example.com',
            'password' => bcrypt('secret'),
        ]);

        $service = new CashRegisterService();

        $service->openRegister($user, [
            'branch_id' => $branch->id,
            'opening_balance' => 150000,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah memiliki shift kasir yang masih terbuka');

        $service->openRegister($user, [
            'branch_id' => $branch->id,
            'opening_balance' => 200000,
        ]);
    }

    public function test_it_rejects_cash_movements_for_non_owner_or_closed_register(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Bandung',
            'code' => 'CB-001',
        ]);

        $owner = User::create([
            'branch_id' => $branch->id,
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('secret'),
        ]);

        $otherUser = User::create([
            'branch_id' => $branch->id,
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => bcrypt('secret'),
        ]);

        $service = new CashRegisterService();
        $register = $service->openRegister($owner, [
            'branch_id' => $branch->id,
            'opening_balance' => 100000,
        ]);

        $register->update(['status' => 'closed']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('tidak dapat menambah transaksi');

        $service->recordCashMovement($otherUser, 'in', [
            'cash_register_id' => $register->id,
            'amount' => 50000,
            'description' => 'Uji',
        ]);
    }
}
