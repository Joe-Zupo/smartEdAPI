<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(BarangaySeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(SchoolTypeSeeder::class);
        $this->call(SchoolSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AcademicYearSeeder::class);
        $this->call(DivisionLeadershipSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(SubmissionSeeder::class);
        $this->call(ResourceDataSeeder::class);
        $this->call(GradeLevelSeeder::class);
        $this->call(EndrollmentDataSeeder::class);
        //$this->call(NotificationSeeder::class);
        $this->call(KpiRateDataSeeder::class);
        $this->call(KpiDataSeeder::class);
        //$this->call(CommentSeeder::class);

    }
}
