<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\TaqatJobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaqatController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(TaqatJobPosting::latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_title' => 'required|string',
            'job_title_ar' => 'required|string',
            'description' => 'required|string',
            'city' => 'required|string',
            'job_type' => 'required|in:full_time,part_time,remote,contract',
            'salary_from' => 'nullable|numeric',
            'salary_to' => 'nullable|numeric',
            'positions_count' => 'integer|min:1',
        ]);

        $posting = TaqatJobPosting::create($data);
        return response()->json(['data' => $posting], 201);
    }

    public function publish(TaqatJobPosting $posting): JsonResponse
    {
        $posting->update(['status' => 'published', 'publish_date' => now()]);
        return response()->json(['message' => 'تم النشر في طاقات', 'data' => $posting]);
    }

    public function close(TaqatJobPosting $posting): JsonResponse
    {
        $posting->update(['status' => 'closed', 'closing_date' => now()]);
        return response()->json(['data' => $posting]);
    }
}
