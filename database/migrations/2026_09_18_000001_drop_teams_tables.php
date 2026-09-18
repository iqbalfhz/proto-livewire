<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the multi-tenancy tables inherited from the starter kit.
     *
     * The site has a single administrator, so teams, memberships and invitations
     * were never used. Guarded with existence checks so a fresh install — which
     * never creates these tables — migrates cleanly too.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'current_team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('current_team_id');
            });
        }

        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The teams feature was removed outright; there is nothing to restore.
    }
};
