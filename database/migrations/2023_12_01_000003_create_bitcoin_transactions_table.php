<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mollsoft\LaravelBitcoinModule\Enums\TransactionCategory;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinAddress;
use Mollsoft\LaravelBitcoinModule\Models\BitcoinWallet;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bitcoin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BitcoinWallet::class, 'wallet_id')
                ->constrained('bitcoin_wallets')
                ->cascadeOnDelete();
            $table->foreignIdFor(BitcoinAddress::class, 'address_id')
                ->constrained('bitcoin_addresses')
                ->cascadeOnDelete();
            $table->string('txid');
            $table->enum('category', TransactionCategory::values());
            $table->decimal('amount', 20, 8);
            $table->unsignedInteger('block_height')
                ->nullable();
            $table->timestamp('time_at');
            $table->unsignedInteger('confirmations');
            $table->timestamps();

            $table->unique(['address_id', 'txid', 'category'], 'unique_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitcoin_transactions');
    }
};
