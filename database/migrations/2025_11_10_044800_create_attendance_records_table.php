<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->date('record_date');
            $table->timestamp('punch_in')->nullable();
            $table->timestamp('punch_out')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->string('status')->default('present'); // ['present', 'absent', 'late', 'leave', 'missed_punch', 'pending_regularization'])->default('present');
            $table->string('biometric_id')->nullable();
            $table->string('device_serial_no')->nullable();
            $table->decimal('late_minutes', 5, 2)->default(0);
            $table->decimal('overtime_minutes', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'record_date']);
            $table->index(['organization_id', 'record_date']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
};
