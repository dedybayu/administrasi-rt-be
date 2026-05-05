<?php

namespace Database\Seeders;

use App\Models\DuesTypeModel;
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
        for ($i = 1; $i <= 20; $i++) {
            $houses[] = HouseModel::create([
                'house_name' => 'Blok A',
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
                // Because end_in_date is not nullable in migration, we use a date far in the future
                'end_in_date' => $now->copy()->addYears(50)->toDateString(), 
                'is_current' => true,
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
    }
}
