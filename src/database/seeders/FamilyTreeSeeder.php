<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Relationship;
use Illuminate\Database\Seeder;

class FamilyTreeSeeder extends Seeder
{
    public function run(): void
    {
        // Generation 1: Grandparents
        $carlos = Person::create([
            'first_name' => 'Carlos',
            'last_name' => 'García',
            'birth_date' => '1950-03-15',
            'gender' => 'male',
            'biography' => 'Fundador de la familia García. Ingeniero civil jubilado.',
        ]);

        $maria = Person::create([
            'first_name' => 'María',
            'last_name' => 'López',
            'birth_date' => '1952-07-22',
            'gender' => 'female',
            'biography' => 'Matriarca de la familia. Profesora de música jubilada.',
        ]);

        // Generation 2: Children of Carlos & María
        $pedro = Person::create([
            'first_name' => 'Pedro',
            'last_name' => 'García López',
            'birth_date' => '1975-01-10',
            'gender' => 'male',
            'biography' => 'Ingeniero en sistemas. Padre de Miguel y Lucía.',
        ]);

        $ana = Person::create([
            'first_name' => 'Ana',
            'last_name' => 'García López',
            'birth_date' => '1978-06-25',
            'gender' => 'female',
            'biography' => 'Arquitecta. Madre de Sofía.',
        ]);

        $juan = Person::create([
            'first_name' => 'Juan',
            'last_name' => 'García López',
            'birth_date' => '1980-11-03',
            'gender' => 'male',
            'biography' => 'Médico cirujano. Padre de Diego y Valentina.',
        ]);

        // Generation 3: Grandchildren
        $miguel = Person::create([
            'first_name' => 'Miguel',
            'last_name' => 'García',
            'birth_date' => '2000-04-18',
            'gender' => 'male',
            'biography' => 'Estudiante de ingeniería.',
        ]);

        $lucia = Person::create([
            'first_name' => 'Lucía',
            'last_name' => 'García',
            'birth_date' => '2003-09-07',
            'gender' => 'female',
            'biography' => 'Estudiante de diseño gráfico.',
        ]);

        $sofia = Person::create([
            'first_name' => 'Sofía',
            'last_name' => 'García',
            'birth_date' => '2005-12-14',
            'gender' => 'female',
            'biography' => 'Estudiante de secundaria.',
        ]);

        $diego = Person::create([
            'first_name' => 'Diego',
            'last_name' => 'García',
            'birth_date' => '2008-02-28',
            'gender' => 'male',
            'biography' => 'Estudiante de primaria.',
        ]);

        $valentina = Person::create([
            'first_name' => 'Valentina',
            'last_name' => 'García',
            'birth_date' => '2010-05-20',
            'gender' => 'female',
            'biography' => 'Estudiante de primaria.',
        ]);

        // Relationships: Carlos & María are parents of Pedro, Ana, Juan
        Relationship::create(['parent_id' => $carlos->id, 'child_id' => $pedro->id]);
        Relationship::create(['parent_id' => $maria->id, 'child_id' => $pedro->id]);
        Relationship::create(['parent_id' => $carlos->id, 'child_id' => $ana->id]);
        Relationship::create(['parent_id' => $maria->id, 'child_id' => $ana->id]);
        Relationship::create(['parent_id' => $carlos->id, 'child_id' => $juan->id]);
        Relationship::create(['parent_id' => $maria->id, 'child_id' => $juan->id]);

        // Pedro is parent of Miguel and Lucía
        Relationship::create(['parent_id' => $pedro->id, 'child_id' => $miguel->id]);
        Relationship::create(['parent_id' => $pedro->id, 'child_id' => $lucia->id]);

        // Ana is parent of Sofía
        Relationship::create(['parent_id' => $ana->id, 'child_id' => $sofia->id]);

        // Juan is parent of Diego and Valentina
        Relationship::create(['parent_id' => $juan->id, 'child_id' => $diego->id]);
        Relationship::create(['parent_id' => $juan->id, 'child_id' => $valentina->id]);
    }
}
