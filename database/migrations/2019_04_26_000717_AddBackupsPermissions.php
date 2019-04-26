<?php

use App\Support\Database\Migration;

class AddBackupsPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->addPermission('pkg.backup.read');
        $this->addPermission('pkg.backup.write');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->deletePermission('pkg.backup.read');
        $this->deletePermission('pkg.backup.write');
    }
}
