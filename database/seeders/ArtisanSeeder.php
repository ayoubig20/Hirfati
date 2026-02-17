<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ArtisanSeeder extends Seeder
{
    /**
     * Service categories available on the platform.
     */
    private const CATEGORIES = [
        'Plumbing',
        'Electrical',
        'Painting',
        'Carpentry',
        'Masonry',
        'HVAC',
        'Roofing',
        'Tiling',
    ];

    /**
     * Moroccan cities where artisans operate.
     */
    private const LOCATIONS = [
        'Casablanca',
        'Rabat',
        'Marrakech',
        'Fes',
        'Tangier',
        'Agadir',
    ];

    /**
     * Service templates per category: [name, description, base_price_range, duration_hours].
     */
    private const SERVICE_TEMPLATES = [
        'Plumbing' => [
            ['Pipe Repair', 'Fix leaking or burst pipes', [150, 400], 2],
            ['Drain Cleaning', 'Unclog and clean drains', [100, 250], 1],
            ['Water Heater Installation', 'Install or replace water heaters', [500, 1200], 4],
        ],
        'Electrical' => [
            ['Wiring Repair', 'Fix faulty wiring and connections', [200, 600], 3],
            ['Light Fixture Installation', 'Install ceiling and wall light fixtures', [100, 300], 1],
            ['Electrical Panel Upgrade', 'Upgrade the main electrical panel', [800, 2000], 6],
        ],
        'Painting' => [
            ['Interior Painting', 'Paint interior walls and ceilings', [300, 800], 8],
            ['Exterior Painting', 'Paint exterior surfaces and facades', [500, 1500], 12],
            ['Decorative Finishing', 'Apply decorative plaster and textures', [400, 1000], 6],
        ],
        'Carpentry' => [
            ['Custom Furniture', 'Build custom wood furniture', [600, 2000], 16],
            ['Door Installation', 'Install or replace interior and exterior doors', [200, 500], 3],
            ['Cabinet Making', 'Build and install kitchen or bathroom cabinets', [800, 2500], 20],
        ],
        'Masonry' => [
            ['Wall Construction', 'Build brick or block walls', [500, 1500], 12],
            ['Concrete Work', 'Pour and finish concrete surfaces', [400, 1200], 8],
            ['Stone Cladding', 'Apply decorative stone to walls', [600, 1800], 10],
        ],
        'HVAC' => [
            ['AC Installation', 'Install split or central air conditioning', [800, 2500], 6],
            ['AC Maintenance', 'Clean and service air conditioning units', [150, 350], 2],
            ['Ventilation Setup', 'Install ventilation and duct systems', [500, 1500], 8],
        ],
        'Roofing' => [
            ['Roof Repair', 'Fix leaks and damaged roof sections', [300, 800], 4],
            ['Roof Waterproofing', 'Apply waterproof membrane to roof', [400, 1200], 6],
            ['Full Roof Replacement', 'Complete tear-off and reroof', [2000, 5000], 24],
        ],
        'Tiling' => [
            ['Floor Tiling', 'Install floor tiles in any room', [200, 600], 6],
            ['Bathroom Tiling', 'Tile bathroom walls and floors', [300, 800], 8],
            ['Zellige Mosaic Work', 'Traditional Moroccan zellige tilework', [500, 1500], 12],
        ],
    ];

    /**
     * Moroccan-style names for realistic seeding.
     */
    private const FIRST_NAMES = [
        'Mohammed', 'Ahmed', 'Youssef', 'Hassan', 'Omar', 'Karim', 'Rachid',
        'Mustapha', 'Abdellah', 'Khalid', 'Said', 'Nabil', 'Hamid', 'Brahim',
        'Driss', 'Fouad', 'Aziz', 'Jamal', 'Mehdi', 'Zakaria', 'Amine',
        'Hicham', 'Samir', 'Reda', 'Tarik', 'Ismail', 'Abdelkader', 'Younes',
        'Lahcen', 'Abdessamad', 'Othmane', 'Adil', 'Anass', 'Badr', 'Soufiane',
        'Ayoub', 'Mounir', 'Noureddine', 'Abderrahim', 'Mouad', 'Bilal',
        'Ilyass', 'Taha', 'Riad', 'Achraf', 'Jawad', 'Walid', 'Houssam',
        'Simohammed', 'Abdellatif',
    ];

    private const LAST_NAMES = [
        'Alaoui', 'Benali', 'El Amrani', 'Tazi', 'Fassi', 'Bennani', 'El Idrissi',
        'Chraibi', 'Berrada', 'Sqalli', 'El Mansouri', 'Benjelloun', 'Lahlou',
        'Ziani', 'Bouzidi', 'El Khattabi', 'Ouazzani', 'Hajji', 'El Moutawakil',
        'Kadiri', 'Bouazza', 'Cherkaoui', 'El Harti', 'Senhaji', 'Naciri',
        'Rahmani', 'El Fassi', 'Mouline', 'Kabbaj', 'El Guerrouj', 'Belkadi',
        'Daoudi', 'Filali', 'Ghazi', 'Haddad', 'Jabri', 'Kettani', 'Lamrani',
        'Mabrouk', 'Najib', 'Qadiri', 'Raissouni', 'Saadi', 'Tahiri', 'Wahbi',
        'Yassine', 'Zerouali', 'Amrani', 'Belhaj', 'Chakir',
    ];

    /**
     * Indices of artisans that will be flagged as fraudulent.
     * These artisans will have high review ratings but extremely low completion rates.
     */
    private const FRAUDULENT_INDICES = [7, 19, 31, 42];

    public function run(): void
    {
        $this->command->info('Seeding 20 customer users...');
        $customers = User::factory()->count(20)->create();

        $this->command->info('Seeding 50 artisans with services...');

        $usedEmails = [];

        for ($i = 0; $i < 50; $i++) {
            $firstName = self::FIRST_NAMES[$i];
            $lastName = self::LAST_NAMES[$i];
            $fullName = "{$firstName} {$lastName}";

            // Create a user account for this artisan
            $email = strtolower($firstName) . '.' . strtolower(str_replace(' ', '', $lastName)) . '@hirfati.ma';

            // Ensure unique email
            if (in_array($email, $usedEmails)) {
                $email = strtolower($firstName) . '.' . strtolower(str_replace(' ', '', $lastName)) . ($i + 1) . '@hirfati.ma';
            }
            $usedEmails[] = $email;

            $artisanUser = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Pick a primary category and location
            $primaryCategory = self::CATEGORIES[$i % count(self::CATEGORIES)];
            $location = self::LOCATIONS[$i % count(self::LOCATIONS)];
            $isFraudulent = in_array($i, self::FRAUDULENT_INDICES);

            // Fraudulent artisans start with a neutral trust score (will be recalculated)
            $initialTrustScore = 0.5000;

            $phone = '06' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $artisan = Artisan::create([
                'user_id' => $artisanUser->id,
                'name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'service_category' => $primaryCategory,
                'specialty' => $this->generateSpecialty($primaryCategory),
                'location' => $location,
                'hourly_rate' => rand(80, 350) + (rand(0, 1) ? 0.50 : 0.00),
                'avg_rating' => 0.00,
                'jobs_completed' => 0,
                'trust_score' => $initialTrustScore,
                'status' => 'active',
            ]);

            // Create 1-3 services for this artisan
            $serviceCount = rand(1, 3);
            $categoryTemplates = self::SERVICE_TEMPLATES[$primaryCategory];
            $selectedTemplates = array_slice($categoryTemplates, 0, $serviceCount);

            foreach ($selectedTemplates as $template) {
                [$name, $description, $priceRange, $duration] = $template;

                Service::create([
                    'artisan_id' => $artisan->id,
                    'category' => $primaryCategory,
                    'name' => $name,
                    'description' => $description,
                    'base_price' => rand($priceRange[0] * 100, $priceRange[1] * 100) / 100,
                    'duration_estimate' => $duration,
                ]);
            }

            $label = $isFraudulent ? ' [FRAUDULENT]' : '';
            $this->command->line("  Created artisan #{$artisan->id}: {$fullName} ({$primaryCategory}, {$location}){$label}");
        }

        $this->command->info('Artisan seeding complete: 50 artisans, 20 customers.');
        $this->command->info('Fraudulent artisan indices (0-based): ' . implode(', ', self::FRAUDULENT_INDICES));
    }

    /**
     * Generate a specialty string based on the primary category.
     */
    private function generateSpecialty(string $category): string
    {
        $specialties = [
            'Plumbing' => ['Residential plumbing', 'Commercial plumbing', 'Emergency repairs', 'Bathroom renovation'],
            'Electrical' => ['Residential wiring', 'Industrial electrical', 'Solar panel installation', 'Smart home wiring'],
            'Painting' => ['Interior design painting', 'Exterior weatherproofing', 'Tadelakt finishing', 'Decorative murals'],
            'Carpentry' => ['Traditional woodwork', 'Modern furniture', 'Moucharabieh screens', 'Kitchen fitting'],
            'Masonry' => ['Traditional construction', 'Restoration work', 'Decorative stonework', 'Foundation work'],
            'HVAC' => ['Central air systems', 'Split unit specialist', 'Industrial ventilation', 'Heat pump installation'],
            'Roofing' => ['Flat roof specialist', 'Terrace waterproofing', 'Traditional tile roofing', 'Insulation expert'],
            'Tiling' => ['Zellige specialist', 'Modern porcelain tiling', 'Bathroom specialist', 'Outdoor paving'],
        ];

        $options = $specialties[$category] ?? ['General specialist'];

        return $options[array_rand($options)];
    }
}
