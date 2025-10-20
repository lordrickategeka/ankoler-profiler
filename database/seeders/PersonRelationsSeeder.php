<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding person relationships...');

        // Get sample persons
        $students = Person::whereJsonContains('classification', 'student')
            ->where('status', 'active')
            ->take(50)
            ->get();

        $adults = Person::where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 25')
            ->take(100)
            ->get();

        $doctors = Person::whereJsonContains('classification', 'staff')
            ->where('status', 'active')
            ->take(10)
            ->get();

        // Check if we have the necessary data
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping relationship seeding.');
            return;
        }

        if ($adults->isEmpty()) {
            $this->command->warn('No adults found. Skipping relationship seeding.');
            return;
        }

        $relationships = [];
        $timestamp = now();

        // Create parent-child relationships
        foreach ($students as $index => $student) {
            if ($index >= count($adults) - 1) break;

            // Assign 1-2 parents per student
            $numParents = rand(1, 2);
            
            for ($i = 0; $i < $numParents; $i++) {
                $parent = $adults[$index * 2 + $i] ?? null;
                if (!$parent) continue;

                // Parent -> Child relationship
                $relationships[] = [
                    'person_id' => $parent->id,
                    'related_person_id' => $student->id,
                    'relationship_type' => 'child',
                    'is_primary' => $i === 0, // First parent is primary
                    'is_emergency_contact' => true,
                    'notes' => $i === 0 ? 'Primary guardian' : 'Secondary guardian',
                    'status' => 'active',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                // Child -> Parent relationship (reverse)
                $relationships[] = [
                    'person_id' => $student->id,
                    'related_person_id' => $parent->id,
                    'relationship_type' => 'parent',
                    'is_primary' => $i === 0,
                    'is_emergency_contact' => true,
                    'notes' => $i === 0 ? 'Primary contact' : 'Secondary contact',
                    'status' => 'active',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        // Create sibling relationships
        for ($i = 0; $i < count($students) - 1; $i += 2) {
            if ($i + 1 >= count($students)) break;

            $sibling1 = $students[$i];
            $sibling2 = $students[$i + 1];

            // Sibling relationships (bidirectional)
            $relationships[] = [
                'person_id' => $sibling1->id,
                'related_person_id' => $sibling2->id,
                'relationship_type' => 'sibling',
                'is_primary' => false,
                'is_emergency_contact' => false,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $relationships[] = [
                'person_id' => $sibling2->id,
                'related_person_id' => $sibling1->id,
                'relationship_type' => 'sibling',
                'is_primary' => false,
                'is_emergency_contact' => false,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        // Assign doctors to students (only if doctors exist)
        if ($doctors->count() > 0) {
            foreach ($students as $index => $student) {
                $doctor = $doctors[$index % $doctors->count()];

                $relationships[] = [
                    'person_id' => $student->id,
                    'related_person_id' => $doctor->id,
                    'relationship_type' => 'doctor',
                    'is_primary' => false,
                    'is_emergency_contact' => false,
                    'notes' => 'Assigned physician',
                    'status' => 'active',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        } else {
            $this->command->warn('No doctors found. Skipping doctor-student relationships.');
        }

        // Create guardian relationships for some students
        $guardiansNeeded = $students->random(min(15, count($students)));
        foreach ($guardiansNeeded as $student) {
            $guardian = $adults->random();

            $relationships[] = [
                'person_id' => $guardian->id,
                'related_person_id' => $student->id,
                'relationship_type' => 'guardian',
                'is_primary' => false,
                'is_emergency_contact' => true,
                'notes' => 'Legal guardian',
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        // Create teacher relationships
        $teachers = $adults->random(min(20, count($adults)));
        foreach ($students->chunk(5) as $chunk) {
            $teacher = $teachers->random();
            
            foreach ($chunk as $student) {
                $relationships[] = [
                    'person_id' => $student->id,
                    'related_person_id' => $teacher->id,
                    'relationship_type' => 'teacher',
                    'is_primary' => false,
                    'is_emergency_contact' => false,
                    'notes' => 'Class teacher',
                    'status' => 'active',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        // Create spouse relationships between some adults
        $marriedAdults = $adults->chunk(2);
        $spouseCount = 0;
        foreach ($marriedAdults as $couple) {
            if ($spouseCount >= 20) break; // Limit to 20 couples
            if (count($couple) < 2) continue;

            $person1 = $couple[0];
            $person2 = $couple[1];

            $relationships[] = [
                'person_id' => $person1->id,
                'related_person_id' => $person2->id,
                'relationship_type' => 'spouse',
                'is_primary' => true,
                'is_emergency_contact' => true,
                'notes' => 'Spouse',
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $relationships[] = [
                'person_id' => $person2->id,
                'related_person_id' => $person1->id,
                'relationship_type' => 'spouse',
                'is_primary' => true,
                'is_emergency_contact' => true,
                'notes' => 'Spouse',
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $spouseCount++;
        }

        // Only proceed if we have relationships to create
        if (empty($relationships)) {
            $this->command->warn('No relationships to create.');
            return;
        }

        // Batch insert all relationships
        $chunks = array_chunk($relationships, 500);
        foreach ($chunks as $chunk) {
            try {
                DB::table('person_relationships')->insert($chunk);
            } catch (\Exception $e) {
                // Skip duplicates
                $this->command->warn('Skipped some duplicate relationships: ' . $e->getMessage());
            }
        }

        $totalCreated = count($relationships);
        $this->command->info("Created {$totalCreated} person relationships!");
        
        // Display statistics
        $this->displayStatistics();
    }

    /**
     * Display relationship statistics
     */
    private function displayStatistics(): void
    {
        $stats = DB::table('person_relationships')
            ->select('relationship_type', DB::raw('COUNT(*) as count'))
            ->groupBy('relationship_type')
            ->orderByDesc('count')
            ->get();

        $this->command->info("\nRelationship Statistics:");
        $this->command->table(
            ['Relationship Type', 'Count'],
            $stats->map(fn($s) => [ucwords(str_replace('_', ' ', $s->relationship_type)), $s->count])
        );

        $primaryCount = DB::table('person_relationships')
            ->where('is_primary', true)
            ->count();

        $emergencyCount = DB::table('person_relationships')
            ->where('is_emergency_contact', true)
            ->count();

        $this->command->info("\nPrimary contacts: {$primaryCount}");
        $this->command->info("Emergency contacts: {$emergencyCount}");
    }
}