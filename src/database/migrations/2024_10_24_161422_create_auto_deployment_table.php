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
        Schema::create('auto_deployment', function (Blueprint $table)
        {
            $table->id();
            $table->string("name")->comment("Webhook Type (Deployment Name)");
            $table->json("webhook_payload")->comment("Post Payload recieved from webhook");
            $table->string("status")->comment("pending, processing, failed, success");
            $table->longText("process_output")->comment("output of ansible");
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_deployment');
    }
};
