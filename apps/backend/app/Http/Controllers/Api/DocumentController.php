<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * إدارة المستندات.
 *
 * الملفات بتتخزن على قرص `local` (storage/app/private/documents) — **مش** في public،
 * وبتتقدّم عبر `GET /{document}/download` اللي بيتحقق من التوكن الأول.
 * السبب: `storage:link` بيعتمد على symlink، والـ symlink معطّل على أغلب
 * الاستضافات المشتركة (منها InfinityFree)، فالتقديم عبر الراوت بيشتغل في كل مكان.
 */
class DocumentController extends Controller
{
    /** الامتدادات المسموح برفعها — قايمة سماح، مش قايمة منع. */
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,webp,gif,zip,dwg';

    public function index(Request $request): JsonResponse
    {
        $documents = Document::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('documentable_type'), fn ($q, $t) => $q->where('documentable_type', $t))
            ->when($request->query('documentable_id'), fn ($q, $id) => $q->where('documentable_id', $id))
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->with('uploader:id,name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($documents);
    }

    /**
     * POST /api/v1/document-management/documents
     *
     * بيقبل حالتين:
     *  1) رفع ملف فعلي: multipart مع الحقل `file` (الحالة العادية من الواجهة).
     *  2) تسجيل مسار موجود مسبقًا: `file_path` نصّي (لملفات مرفوعة بطريقة تانية).
     *
     * documentable_type بياخد اسم الموديل كامل، مثلاً: App\Models\Project
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required_without:file_path|file|max:8192|mimes:'.self::ALLOWED_EXTENSIONS,
            'file_path' => 'required_without:file|nullable|string|max:500',
            'mime_type' => 'nullable|string|max:100',
            'file_size' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:100',
            'documentable_type' => 'nullable|string|max:255',
            'documentable_id' => 'nullable|integer',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // store() بيولّد اسم عشوائي — فالاسم الأصلي مبيوصلش لنظام الملفات
            $validated['file_path'] = $file->store('documents');
            $validated['mime_type'] = $file->getClientMimeType();
            $validated['file_size'] = $file->getSize();
            $validated['original_name'] = $file->getClientOriginalName();
        }

        unset($validated['file']);

        $document = Document::create([
            ...$validated,
            'uploaded_by' => $request->user()?->id,
        ]);

        return response()->json($this->present($document->fresh('uploader')), 201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json($this->present($document->load('uploader', 'documentable')));
    }

    /**
     * GET /api/v1/document-management/documents/{document}/download
     * بيرجّع الملف نفسه. محمي بـ auth:sanctum زي باقي الراوتس.
     */
    public function download(Document $document): StreamedResponse|JsonResponse
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return response()->json(['message' => 'الملف مش موجود على السيرفر.'], 404);
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name ?: $document->title,
        );
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
        ]);

        $document->update($validated);

        return response()->json($this->present($document));
    }

    public function destroy(Document $document): JsonResponse
    {
        // الملف بيتمسح مع السجل — مفيش داعي لملفات يتيمة تاكل مساحة
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(null, 204);
    }

    /** بيضيف رابط التحميل للاستجابة عشان الواجهة تعرضه من غير ما تبني المسار بنفسها. */
    private function present(Document $document): array
    {
        return [
            ...$document->toArray(),
            'download_url' => url("/api/v1/document-management/documents/{$document->id}/download"),
        ];
    }
}
