<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QHUB extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('.\db.sql'); 
        $sql = File::get($path); 
        DB::unprepared($sql); 
        $this->command->info('tabel berhasil pulih dari SQL!');    
    }
}
