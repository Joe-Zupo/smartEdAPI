<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Helpers\SeederFileTrait;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{

    use SeederFileTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->cleanupSeederFiles('announcements');
        
        $publicAnnouncements = [
            [
                'title' => 'SA DEPED, WALANG PUWANG ANG KORAPSYON!',
                'description' => 'Mahigpit na ipinatutupad sa DepEd ang zero tolerance policy sa ano mang uri ng korapsyon. The Department of Education condemns in the strongest terms all forms of corruption involving appointments, promotions, and designations.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(1),
            ],
            [
                'title' => 'Brigada Eskwela 2026 Kick-off',
                'description' => 'Join us as we prepare our schools for the opening of classes. Volunteers, parents, and stakeholders are invited to participate in school clean-up and minor repair activities.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(3),
            ],
            [
                'title' => 'Early Registration Schedule for SY 2026-2027',
                'description' => 'Public schools will conduct early registration from January 20 to February 15. Parents and guardians are encouraged to enroll learners early for better class planning.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(5),
            ],
            [
                'title' => 'Division Robotics Training Program Opens',
                'description' => 'The division office is opening slots for robotics training aimed at science and mathematics club advisers and selected student leaders from participating schools.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(7),
            ],
            [
                'title' => 'Math Fair 2026 Regional Preparation Meeting',
                'description' => 'Coordinators and mathematics leaders are invited to the virtual preparation meeting to align activities, selection mechanics, and event logistics.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(9),
            ],
            [
                'title' => 'School Supplies Assistance Distribution',
                'description' => 'Distribution of school supply kits for identified priority learners starts this week. Beneficiaries may coordinate with their school focal persons for claiming schedules.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(12),
            ],
            [
                'title' => 'Community Reading Camps Launch',
                'description' => 'Reading camps for beginning readers will be launched in partner barangays to support literacy goals for Key Stage 1 learners.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(15),
            ],
            [
                'title' => 'National Learning Camp Enrollment Reminder',
                'description' => 'Schools are advised to complete learner profiling and finalize enrollment lists for the National Learning Camp implementation period.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(18),
            ],
            [
                'title' => 'Teachers’ Month Celebration Activities',
                'description' => 'A month-long celebration recognizing teachers will feature classroom innovation sharing, wellness sessions, and appreciation events across districts.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(21),
            ],
            [
                'title' => 'Child Protection Awareness Campaign',
                'description' => 'Schools will conduct awareness sessions for learners, parents, and personnel to reinforce child protection policies and reporting mechanisms.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(24),
            ],
            [
                'title' => 'Nutrition Month School-Based Activities',
                'description' => 'Schools are encouraged to implement nutrition promotion activities, including healthy baon advocacy and school garden support projects.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(27),
            ],
            [
                'title' => 'Public Advisory on Class Suspension Protocols',
                'description' => 'This advisory reiterates class suspension protocols during severe weather and emergencies, including official communication channels and updates.',
                'image_url' => $this->copySeederFile('announcements', 'sample_announcement.png', 'announcements/2026-2027'),
                'created_at' => now()->subDays(30),
            ],
        ];

        $dashboardAnnouncements = [
            [
                'title' => 'Quarterly Planning Meeting - Q1 2026',
                'description' => 'All school heads and planning officers are requested to attend the quarterly planning meeting at the Division Office Conference Hall.',
                'created_at' => now()->subHours(3),
            ],
            [
                'title' => 'Submission Deadline Extended',
                'description' => 'The deadline for Enrollment Data submission has been extended. Ensure all entries are validated before final submission.',
                'created_at' => now()->subDays(2),
            ],
            [
                'title' => 'KPI Encoding Window Open',
                'description' => 'The KPI encoding window is now open for all authorized accounts. Please complete entries before the system cutoff date.',
                'created_at' => now()->subDays(4),
            ],
            [
                'title' => 'Resource Data Validation Reminder',
                'description' => 'Schools are reminded to verify and update resource data records to avoid inconsistencies during division-level consolidation.',
                'created_at' => now()->subDays(6),
            ],
            [
                'title' => 'School Profile Update Schedule',
                'description' => 'The school profile update module will be available by district schedule this week. Please prepare required supporting documents.',
                'created_at' => now()->subDays(8),
            ],
            [
                'title' => 'Division Memo Upload Compliance Check',
                'description' => 'Please review pending memo acknowledgements and upload required attachments for compliance tracking.',
                'created_at' => now()->subDays(10),
            ],
            [
                'title' => 'Academic Year Default Setting Notice',
                'description' => 'Admins are advised to confirm the default academic year setting before processing new submissions and reports.',
                'created_at' => now()->subDays(12),
            ],
            [
                'title' => 'System Maintenance Advisory',
                'description' => 'The system will undergo scheduled maintenance this weekend. Access may be intermittent during the maintenance window.',
                'created_at' => now()->subDays(14),
            ],
        ];

        foreach ($publicAnnouncements as $announcement) {
            Announcement::create([
                'title' => $announcement['title'],
                'description' => $announcement['description'],
                'type' => 'public',
                'image_url' => $announcement['image_url'],
                'created_at' => $announcement['created_at'],
            ]);
        }

        foreach ($dashboardAnnouncements as $announcement) {
            Announcement::create([
                'title' => $announcement['title'],
                'description' => $announcement['description'],
                'type' => 'dashboard',
                'image_url' => null,
                'created_at' => $announcement['created_at'],
            ]);
        }
    }
}
