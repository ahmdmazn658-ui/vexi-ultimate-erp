<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * إدارة قنوات الحجز (Channel Manager). المزامنة الفعلية مع أي مزوّد
 * خارجي (Booking.com, Expedia...) محتاجة API credentials حقيقية من
 * المزوّد نفسه — مش حاجة نقدر نبنيها من غير حساب فعلي عند المزوّد.
 * الـ endpoint هنا بيدي البنية الجاهزة للـ CRUD + نقطة sync تقدر
 * تربطها بمكتبة/HTTP client المزوّد لما يتحدد.
 */
class ChannelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $channels = Channel::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->withCount('reservations')
            ->orderBy('name')
            ->get()
            ->makeHidden('config'); // إخفاء بيانات الاتصال من قوائم العرض العامة

        return response()->json($channels);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hotel_channels,code',
            'provider' => 'nullable|string|max:100',
            'config' => 'nullable|array',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        return response()->json(Channel::create($validated), 201);
    }

    public function update(Request $request, Channel $channel): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'config' => 'nullable|array',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $channel->update($validated);

        return response()->json($channel->makeHidden('config'));
    }

    /**
     * نقطة مزامنة عامة — دلوقتي بتحدّث last_synced_at بس. لما يتحدد المزوّد
     * (Booking.com API, Expedia API...) هنا مكان إضافة الـ HTTP client
     * الفعلي اللي بيسحب الحجوزات الجديدة ويبعت تحديثات الإتاحة/الأسعار.
     */
    public function sync(Channel $channel): JsonResponse
    {
        if (! $channel->provider) {
            return response()->json([
                'message' => 'القناة دي مالهاش مزوّد API مربوط — مزامنة يدوية بس.',
            ], 422);
        }

        // TODO: استدعاء HTTP client حقيقي للمزوّد لما يتحدد ويتوفر API credentials.
        $channel->update(['last_synced_at' => now()]);

        return response()->json([
            'message' => 'تم تحديث وقت آخر مزامنة. التنفيذ الفعلي للمزوّد ('.$channel->provider.') لسه محتاج ربط API.',
            'channel' => $channel->makeHidden('config'),
        ]);
    }

    public function destroy(Channel $channel): JsonResponse
    {
        $channel->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
