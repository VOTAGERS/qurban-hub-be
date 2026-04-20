<?php

use Illuminate\Database\Seeder;


class QHUB extends Seeder {
    public function run(): void
    {
        $path = database_path('.\db.sql'); 
        $sql = File::get($path); 
        DB::unprepared($sql); 
        $this->command->info('tabel berhasil pulih dari SQL!');    
    }

}