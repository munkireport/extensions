<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class ExtensionsAddTeamid extends Migration
{
    private $tableName = 'extensions';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('developer')->after('path')->nullable();
            $table->string('teamid')->after('developer')->nullable();
            $table->index('teamid');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('developer');
            $table->dropColumn('teamid');
        });
    }
}