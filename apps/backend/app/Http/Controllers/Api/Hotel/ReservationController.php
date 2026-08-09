<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Hotel\Reservation;
use App\Models\Hotel\Room;
use App\Support\Hotel\FolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(private readonly FolioService $folioService) {}

    public function index(Request $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('date'), fn ($q, $d) => $q->where('check_in_date', '<=', $d)->where('check_out_date', '>', $d))
            ->with(['guest:id,full_name,phone', 'channel:id,name,code', 'rooms:id,room_number'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($reservations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_guest_id' => 'required|exists:hotel_guests,id',
            'hotel_channel_id' => 'nullable|exists:hotel_channels,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'nullable|integer|min:0|max:20',
            'special_requests' => 'nullable|string',
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:hotel_rooms,id',
        ]);

        return response()->json(
            DB::transaction(function () use ($validated, $request) {
                $rooms = Room::whereIn('id', $validated['room_ids'])->get();

                foreach ($rooms as $room) {
                    if (! $room->isAvailableBetween($validated['check_in_date'], $validated['check_out_date'])) {
                        throw ValidationException::withMessages([
                            'room_ids' => "الغرفة {$room->room_number} محجوزة بالفعل في الفترة دي.",
                        ]);
                    }
                }

                $reservation = Reservation::create([
                    'confirmation_number' => 'RES-'.Str::upper(Str::random(8)),
                    'hotel_guest_id' => $validated['hotel_guest_id'],
                    'hotel_channel_id' => $validated['hotel_channel_id'] ?? null,
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'adults' => $validated['adults'],
                    'children' => $validated['children'] ?? 0,
                    'status' => 'confirmed',
                    'special_requests' => $validated['special_requests'] ?? null,
                    'created_by' => $request->user()?->id,
                ]);

                foreach ($rooms as $room) {
                    $reservation->reservationRooms()->create([
                        'hotel_room_id' => $room->id,
                        'rate_per_night' => $room->roomType->base_rate,
                    ]);
                }

                return $reservation->load(['guest', 'rooms']);
            }),
            201
        );
    }

    public function show(Reservation $reservation): JsonResponse
    {
        return response()->json($reservation->load(['guest', 'channel', 'rooms', 'folio.charges', 'posOrders']));
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $request->validate([
            'special_requests' => 'nullable|string',
            'status' => 'sometimes|in:tentative,confirmed,cancelled,no_show',
        ]);

        $reservation->update($validated);

        return response()->json($reservation);
    }

    /** تسجيل وصول: بيقفل حالة الحجز، بيفتح الـ folio، وبيحوّل الغرف لـ occupied. */
    public function checkIn(Reservation $reservation): JsonResponse
    {
        if ($reservation->status !== 'confirmed') {
            throw ValidationException::withMessages(['status' => 'الحجز لازم يكون confirmed قبل تسجيل الوصول.']);
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => 'checked_in']);
            $this->folioService->openForReservation($reservation);

            Room::whereIn('id', $reservation->reservationRooms()->pluck('hotel_room_id'))
                ->update(['status' => 'occupied_clean']);
        });

        return response()->json($reservation->fresh(['folio.charges', 'rooms']));
    }

    /**
     * تسجيل مغادرة: بيقفل الـ folio ويولّد فاتورة محاسبية، بيحرر الغرف
     * (بحالة vacant_dirty عشان الـ housekeeping تنضفها)، وبيقفل الحجز.
     */
    public function checkOut(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->status !== 'checked_in') {
            throw ValidationException::withMessages(['status' => 'الحجز لازم يكون checked_in قبل تسجيل المغادرة.']);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $invoice = DB::transaction(function () use ($reservation, $validated) {
            $folio = $reservation->folio ?? $this->folioService->openForReservation($reservation);
            $customer = isset($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;

            $invoice = $this->folioService->closeAndInvoice($folio, $customer);

            $reservation->update(['status' => 'checked_out']);

            Room::whereIn('id', $reservation->reservationRooms()->pluck('hotel_room_id'))
                ->update(['status' => 'vacant_dirty']);

            foreach ($reservation->reservationRooms as $resRoom) {
                $resRoom->room->housekeepingTasks()->create([
                    'task_type' => 'cleaning',
                    'status' => 'pending',
                    'priority' => 'normal',
                    'due_at' => now()->addHours(2),
                ]);
            }

            return $invoice;
        });

        return response()->json([
            'reservation' => $reservation->fresh(['rooms', 'folio']),
            'invoice' => $invoice,
        ]);
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->update(['status' => 'cancelled']);

        return response()->json(null, 204);
    }
}
