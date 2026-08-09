<?php

namespace Database\Seeders;

use App\Models\Hotel\PosOutlet;
use App\Models\Hotel\PosProduct;
use App\Models\Hotel\Room;
use App\Models\Hotel\RoomType;
use Illuminate\Database\Seeder;

/** بيانات تجريبية لموديول الفندقة — تشغّلها بـ: php artisan db:seed --class=HotelSeeder */
class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $standard = RoomType::firstOrCreate(
            ['name' => 'Standard'],
            ['name_ar' => 'قياسية', 'max_occupancy' => 2, 'base_rate' => 350]
        );

        $deluxe = RoomType::firstOrCreate(
            ['name' => 'Deluxe'],
            ['name_ar' => 'ديلوكس', 'max_occupancy' => 3, 'base_rate' => 550]
        );

        $suite = RoomType::firstOrCreate(
            ['name' => 'Suite'],
            ['name_ar' => 'جناح', 'max_occupancy' => 4, 'base_rate' => 950]
        );

        foreach (range(101, 110) as $n) {
            Room::firstOrCreate(['room_number' => (string) $n], ['hotel_room_type_id' => $standard->id, 'floor' => '1']);
        }
        foreach (range(201, 206) as $n) {
            Room::firstOrCreate(['room_number' => (string) $n], ['hotel_room_type_id' => $deluxe->id, 'floor' => '2']);
        }
        foreach (range(301, 303) as $n) {
            Room::firstOrCreate(['room_number' => (string) $n], ['hotel_room_type_id' => $suite->id, 'floor' => '3']);
        }

        $restaurant = PosOutlet::firstOrCreate(['name' => 'المطعم الرئيسي'], ['type' => 'restaurant']);
        $minibar = PosOutlet::firstOrCreate(['name' => 'Minibar'], ['type' => 'minibar']);

        PosProduct::firstOrCreate(['hotel_pos_outlet_id' => $restaurant->id, 'name' => 'إفطار بوفيه'], ['category' => 'وجبات', 'price' => 65]);
        PosProduct::firstOrCreate(['hotel_pos_outlet_id' => $restaurant->id, 'name' => 'عشاء'], ['category' => 'وجبات', 'price' => 95]);
        PosProduct::firstOrCreate(['hotel_pos_outlet_id' => $minibar->id, 'name' => 'مياه معدنية'], ['category' => 'مشروبات', 'price' => 12]);
        PosProduct::firstOrCreate(['hotel_pos_outlet_id' => $minibar->id, 'name' => 'عصير طازج'], ['category' => 'مشروبات', 'price' => 18]);
    }
}
