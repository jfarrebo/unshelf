<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Novel·la',
            'Ciència-ficció',
            'Fantasia',
            'Misteri i thriller',
            'Romàntica',
            'Històrica',
            'Poesia',
            'Teatre',
            'Assaig',
            'Biografia',
            'Infantil',
            'Juvenil',
            'Còmic i novel·la gràfica',
            'Divulgació',
            'Autoajuda',
            'Cuina',
            'Viatges',
            'Llibres de text',
        ];

        foreach ($genres as $name) {
            DB::table('genres')->updateOrInsert(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
