<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

class PackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = [
            [
                'name' => 'Premium Vitamin C Complex',
                'price' => 1500.00,
                'description' => 'High-quality vitamin C supplement with bioflavonoids for enhanced absorption and immune support.',
                'features' => [
                    '1000mg Vitamin C per serving',
                    'Natural bioflavonoids',
                    'Immune system support',
                    'Antioxidant properties',
                    '60 capsules per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Omega-3 Fish Oil Premium',
                'price' => 2200.00,
                'description' => 'Pure omega-3 fatty acids from wild-caught fish, supporting heart and brain health.',
                'features' => [
                    '1000mg EPA + DHA per softgel',
                    'Wild-caught Norwegian salmon',
                    'Heart health support',
                    'Brain function enhancement',
                    '120 softgels per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Multi-Vitamin Advanced Formula',
                'price' => 1800.00,
                'description' => 'Comprehensive multivitamin with minerals and antioxidants for daily nutritional support.',
                'features' => [
                    '23 essential vitamins & minerals',
                    'Antioxidant blend',
                    'Energy metabolism support',
                    'Immune system boost',
                    '90 tablets per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Premium Arabica Coffee Blend',
                'price' => 1200.00,
                'description' => 'Gourmet arabica coffee beans with natural energy boost and antioxidant properties.',
                'features' => [
                    '100% Arabica beans',
                    'Medium roast',
                    'Natural caffeine source',
                    'Rich antioxidant content',
                    '500g ground coffee'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Collagen Protein Powder',
                'price' => 2500.00,
                'description' => 'Hydrolyzed collagen peptides for skin, hair, nail, and joint health.',
                'features' => [
                    'Type I & III collagen',
                    'Hydrolyzed for better absorption',
                    'Skin elasticity support',
                    'Joint health maintenance',
                    '500g unflavored powder'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Probiotic Complex 50 Billion',
                'price' => 1900.00,
                'description' => 'Advanced probiotic formula with multiple strains for gut health and immunity.',
                'features' => [
                    '50 billion CFUs per capsule',
                    '15 probiotic strains',
                    'Digestive health support',
                    'Immune system enhancement',
                    '60 delayed-release capsules'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Magnesium Complex Plus',
                'price' => 1300.00,
                'description' => 'Complete magnesium supplement with multiple forms for better absorption and utilization.',
                'features' => [
                    '400mg magnesium per serving',
                    'Multiple magnesium forms',
                    'Muscle relaxation support',
                    'Nervous system health',
                    '120 capsules per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Green Coffee Bean Extract',
                'price' => 1600.00,
                'description' => 'Natural green coffee bean extract for metabolism support and antioxidant benefits.',
                'features' => [
                    '800mg green coffee extract',
                    '50% chlorogenic acid',
                    'Metabolism support',
                    'Natural antioxidant',
                    '90 capsules per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin D3 + K2 Complex',
                'price' => 1400.00,
                'description' => 'High-potency vitamin D3 with K2 for optimal bone health and calcium utilization.',
                'features' => [
                    '5000 IU Vitamin D3',
                    '100mcg Vitamin K2',
                    'Bone health support',
                    'Calcium absorption',
                    '120 softgels per bottle'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Turmeric Curcumin Plus',
                'price' => 1700.00,
                'description' => 'Enhanced curcumin formula with black pepper extract for maximum absorption and anti-inflammatory benefits.',
                'features' => [
                    '1500mg curcumin per serving',
                    'Black pepper extract (BioPerine)',
                    'Anti-inflammatory properties',
                    'Joint health support',
                    '60 capsules per bottle'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}