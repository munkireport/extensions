<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;

class ExtensionsRemoveCodesign extends Migration
{
    private $tableName = 'extensions';

    public function up()
    {
        $capsule = new Capsule();
        if ($capsule::schema()->hasColumn($this->tableName, 'codesign')){
            $capsule::schema()->table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('codesign');
            });
        }
    }

    public function down()
    {
        // No going back
    }
}