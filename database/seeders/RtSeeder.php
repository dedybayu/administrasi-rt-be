<?php

namespace Database\Seeders;

use App\Models\DuesTypeModel;
use App\Models\PaymentModel;
use App\Models\ExpenseModel;
use Illuminate\Database\Seeder;
use App\Models\HouseModel;
use App\Models\OccupantModel;
use App\Models\HouseOccupantModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class RtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $faker = Faker::create('id_ID');

        // 1. Create 20 houses
        $houses = [];
        $blockNames = ['Cendana', 'Bougenville', 'Flamboyan', 'Mahoni', 'Anggrek'];
        for ($i = 1; $i <= 20; $i++) {
            $nameIndex = floor(($i - 1) / 4); // 5 blocks of 4 houses
            $numInBlock = ($i - 1) % 4 + 1;
            $houses[] = HouseModel::create([
                'house_name' => $blockNames[$nameIndex] . ' ' . $numInBlock,
                'house_number' => str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        // 2. Create 15 permanent occupants (tetap)
        $permanentOccupants = [];
        for ($i = 1; $i <= 15; $i++) {
            $occupant = OccupantModel::create([
                'occupant_name' => $faker->name,
                'occupant_ktp_photo' => 'ktp_tetap_' . $i . '.jpg',
                'occupant_status' => 'tetap',
                'occupant_phone_number' => $faker->phoneNumber,
                'is_married' => $i % 2 == 0,
            ]);
            $permanentOccupants[] = $occupant;

            // Assign to house (Houses 1 to 15)
            HouseOccupantModel::create([
                'house_id' => $houses[$i - 1]->house_id,
                'occupant_id' => $occupant->occupant_id,
                'start_in_date' => $now->copy()->subYears(rand(1, 5))->toDateString(),
                // Because end_in_date is nullable in migration, null is fine but seeder used far future before
                'end_in_date' => null, 
                'is_current' => true,
                'is_head_family' => true,
            ]);
        }

        // 3. Create 3 contract occupants (kontrak) for houses 16, 17, and 18
        $contractOccupants = [];
        for ($i = 1; $i <= 3; $i++) {
            $occupant = OccupantModel::create([
                'occupant_name' => $faker->name,
                'occupant_ktp_photo' => 'ktp_kontrak_' . $i . '.jpg',
                'occupant_status' => 'kontrak',
                'occupant_phone_number' => $faker->phoneNumber,
                'is_married' => false,
            ]);
            $contractOccupants[] = $occupant;

            HouseOccupantModel::create([
                'house_id' => $houses[15 + $i - 1]->house_id,
                'occupant_id' => $occupant->occupant_id,
                'start_in_date' => $now->copy()->subMonths(rand(1, 6))->toDateString(),
                'end_in_date' => $now->copy()->addMonths(rand(1, 6))->toDateString(),
                'is_current' => true,
                'is_head_family' => true,
            ]);
        }
        // House 19 and 20 are left completely empty

        // 4. Create 1 User for Ketua RT, using the first permanent occupant
        UserModel::create([
            'username' => 'ketuart',
            'password' => Hash::make('password123'),
            'is_rt' => true,
            'occupant_id' => $permanentOccupants[0]->occupant_id,
        ]);

        // 5. Create User accounts for the remaining 14 permanent occupants
        for ($i = 1; $i < 15; $i++) {
            UserModel::create([
                'username' => 'warga' . ($i + 1),
                'password' => Hash::make('warga123'),
                'is_rt' => false,
                'occupant_id' => $permanentOccupants[$i]->occupant_id,
            ]);
        }

        // 6. Create User accounts for the 3 contract occupants
        foreach ($contractOccupants as $index => $occupant) {
            UserModel::create([
                'username' => 'warga' . (15 + $index + 1),
                'password' => Hash::make('warga123'),
                'is_rt' => false,
                'occupant_id' => $occupant->occupant_id,
            ]);
        }

        // 7. Create dues types
        DuesTypeModel::create([
            'dues_type_name' => 'Iuran Satpam',
            'dues_type_amount' => 100000,
        ]);

        DuesTypeModel::create([
            'dues_type_name' => 'Iuran Kebersihan',
            'dues_type_amount' => 15000,
        ]);

        // 8. Create payments for permanent occupants
        foreach ($permanentOccupants as $index => $occupant) {
            // Get the house occupant record to get house_occupant_id
            $houseOccupant = HouseOccupantModel::where('occupant_id', $occupant->occupant_id)
                ->where('is_current', true)
                ->first();

            if (!$houseOccupant) continue;

            // 12 months of payment history
            for ($i = 0; $i < 12; $i++) {
                $date = $now->copy()->subMonths($i);
                // Randomly decide if this payment is successful (success) or failed/unpaid (rejected)
                $isSuccess = rand(1, 100) <= rand(60, 90);
                
                // We create a record for each dues type per month
                foreach ([1, 2] as $typeId) {
                    $amount = ($typeId == 1) ? 100000 : 15000;
                    
                    PaymentModel::create([
                        'dues_type_id' => $typeId,
                        'payer_occupant_id' => $occupant->occupant_id,
                        'house_occupant_id' => $houseOccupant->house_occupant_id,
                        'payment_amount' => $isSuccess ? $amount : 0,
                        'payment_date' => $date->toDateString(),
                        'payment_period_month' => $date->month,
                        'payment_period_year' => $date->year,
                        'payment_status' => $isSuccess ? 'success' : 'rejected',
                    ]);
                }
            }
        }

        // 9. Create sample expenses for the last 12 months
        $expenseItems = [
            ['name' => 'Gaji Satpam', 'amount' => 1500000],
            ['name' => 'Biaya Kebersihan Lingkungan', 'amount' => 500000],
            ['name' => 'Listrik Fasilitas Umum', 'amount' => 300000],
            ['name' => 'Perbaikan Lampu Jalan', 'amount' => 200000, 'random' => true],
            ['name' => 'Kegiatan Kerja Bakti', 'amount' => 150000, 'random' => true],
        ];

        for ($i = 0; $i < 12; $i++) {
            $date = $now->copy()->subMonths($i);
            foreach ($expenseItems as $item) {
                // If random is true, only create 50% of the time
                if (isset($item['random']) && $item['random'] && rand(0, 1) === 0) continue;

                ExpenseModel::create([
                    'expense_description' => $item['name'] . ' - ' . $date->format('F Y'),
                    'expense_amount' => $item['amount'],
                    'expense_date' => $date->copy()->startOfMonth()->addDays(rand(1, 28))->toDateString(),
                ]);
            }
        }

        // 10. Create 5 inactive occupants (riwayat)
        for ($i = 1; $i <= 5; $i++) {
            $occupant = OccupantModel::create([
                'occupant_name' => $faker->name . ' (Mantan)',
                'occupant_ktp_photo' => 'ktp_inactive_' . $i . '.jpg',
                'occupant_status' => 'kontrak',
                'occupant_phone_number' => $faker->phoneNumber,
                'is_married' => $i % 2 == 0,
            ]);
            
            HouseOccupantModel::create([
                'house_id' => $houses[$i - 1]->house_id, // Assign to houses 1-5
                'occupant_id' => $occupant->occupant_id,
                'start_in_date' => $now->copy()->subYears(10)->toDateString(),
                'end_in_date' => $now->copy()->subYears(5)->toDateString(),
                'is_current' => false,
                'is_head_family' => false,
            ]);
        }
    }
}
