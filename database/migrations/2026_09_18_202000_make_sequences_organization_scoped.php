<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::dropIfExists('sequences');

            Schema::create('sequences', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id');
                $table->string('name');
                $table->unsignedBigInteger('last_value')->default(0);
                $table->unsignedBigInteger('increment_by')->default(1);
                $table->string('prefix')->nullable();
                $table->string('suffix')->nullable();
                $table->integer('pad_length')->default(0);
                $table->timestamps();

                $table->primary(['organization_id', 'name']);
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            });

            $this->seedSequencesForExistingOrganizations();

            return;
        }

        Schema::table('sequences', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable();
        });

        DB::table('sequences')->update(['organization_id' => DB::table('organizations')->min('id')]);

        Schema::table('sequences', function (Blueprint $table) {
            $table->dropPrimary('sequences_name_primary');
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->primary(['organization_id', 'name']);
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        $this->seedSequencesForExistingOrganizations();
    }

    protected function seedSequencesForExistingOrganizations(): void
    {
        $organizationIds = DB::table('organizations')->pluck('id');

        $defaults = [
            ['name' => 'journal_entry_ref', 'prefix' => 'JE-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'invoice_number', 'prefix' => 'INV-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'purchase_order', 'prefix' => 'PO-', 'pad_length' => 6, 'last_value' => 0],
            ['name' => 'payment_voucher', 'prefix' => 'PV-', 'pad_length' => 6, 'last_value' => 0],
        ];

        foreach ($organizationIds as $organizationId) {
            foreach ($defaults as $default) {
                DB::table('sequences')->updateOrInsert(
                    ['organization_id' => $organizationId, 'name' => $default['name']],
                    $default
                );
            }
        }
    }

    public function down()
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('sequences', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropPrimary('sequences_organization_id_name_primary');
                $table->dropColumn('organization_id');
                $table->primary('name');
            });
        }
    }
};
