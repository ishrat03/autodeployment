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
        Schema::create('auto_deployments', function (Blueprint $table)
        {
            $table->id();
            $table->string("name")->nullable(true)->comment("Webhook Type (Deployment Name)");
            $table->json("webhook_payload")->comment("Post Payload recieved from webhook");
            $table->string("status")->comment("pending, processing, failed, success");
            $table->timestamp("webhook_time")->comment("Time when webhook recieved");
            $table->timestamp("deployment_start_time")->nullable(true)->comment("Time when deployment started");
            $table->timestamp("deployment_end_time")->nullable(true)->comment("Time when deployment ended");
            $table->longText("process_output")->nullable(true)->comment("output of ansible");
            $table->json('json_output')->nullable(true)->comment("output of ansible playbook");
            $table->timestamp('created_at')->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_deployments');
    }
};
