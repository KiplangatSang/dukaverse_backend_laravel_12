<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs("ownerable");
            $table->bigInteger("subscriptionable_id")->nullable();
            $table->string("subscriptionable_type")->nullable();
            $table->foreignId('transaction_id')->references('id')->on('transactions')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->double("paid_amount")->nullable();
            $table->string("subscription_name");
            $table->string("subscription_description");
            $table->string("subscription_duration");
            $table->bigInteger("retail_id")->nullable();
            $table->bigInteger("tier_id");
            $table->string("subscription_price");
            $table->bigInteger("subscription_level")->default(1);
            $table->string("subscription_discount")->default(0);
            $table->string("subscription_categories")->default(1);
            $table->boolean("is_renewable")->default(false);
            $table->boolean("is_active")->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
