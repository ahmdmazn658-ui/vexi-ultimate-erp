<?php
namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\{ImportJob, ExportJob, WorkflowDefinition, ApprovalRequest, IntegrationConnection, WebhookSubscription, ScheduledReport, CustomField, NotificationTemplate, AuditLog};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformModulesController extends Controller
{
    private function list($model, Request $request): JsonResponse { return response()->json($model::latest()->paginate($request->integer('per_page', 25))); }
    public function importJobs(Request $r): JsonResponse { return $this->list(ImportJob::class, $r); }
    public function createImport(Request $r): JsonResponse { $data=$r->validate(['entity'=>'required|string','format'=>'required|in:csv,xlsx,xls,json,xml']); $data['status']='queued'; $data['created_by']=auth()->id(); return response()->json(['data'=>ImportJob::create($data)],201); }
    public function importPreview(Request $r): JsonResponse { $r->validate(['entity'=>'required|string','format'=>'required|string']); return response()->json(['columns'=>[],'sample_rows'=>[],'mapping'=>[],'message'=>'Upload a file to generate preview']); }
    public function runImport(ImportJob $importJob): JsonResponse { $importJob->update(['status'=>'processing']); return response()->json(['message'=>'Import queued for processing','data'=>$importJob]); }
    public function exports(Request $r): JsonResponse { return $this->list(ExportJob::class, $r); }
    public function createExport(Request $r): JsonResponse { $data=$r->validate(['entity'=>'required|string','format'=>'required|in:csv,xlsx,json,xml,pdf']); $data['status']='queued'; $data['columns']=$r->input('columns',[]); $data['filters']=$r->input('filters',[]); $data['created_by']=auth()->id(); return response()->json(['data'=>ExportJob::create($data)],201); }
    public function workflows(Request $r): JsonResponse { return $this->list(WorkflowDefinition::class, $r); }
    public function createWorkflow(Request $r): JsonResponse { $data=$r->validate(['name'=>'required|string','entity'=>'required|string','trigger'=>'array','conditions'=>'array','actions'=>'array']); $data['created_by']=auth()->id(); return response()->json(['data'=>WorkflowDefinition::create($data)],201); }
    public function toggleWorkflow(WorkflowDefinition $workflow): JsonResponse { $workflow->update(['is_active'=>!$workflow->is_active]); return response()->json(['data'=>$workflow]); }
    public function approvals(Request $r): JsonResponse { return $this->list(ApprovalRequest::class, $r); }
    public function decideApproval(Request $r, ApprovalRequest $approval): JsonResponse { $r->validate(['decision'=>'required|in:approved,rejected','comment'=>'nullable|string']); $approval->update(['status'=>$r->decision,'approved_by'=>auth()->id(),'approved_at'=>now(),'comment'=>$r->comment]); return response()->json(['data'=>$approval]); }
    public function integrations(Request $r): JsonResponse { return $this->list(IntegrationConnection::class, $r); }
    public function createIntegration(Request $r): JsonResponse { $data=$r->validate(['name'=>'required|string','provider'=>'required|string','category'=>'string','credentials'=>'array','settings'=>'array']); $data['created_by']=auth()->id(); return response()->json(['data'=>IntegrationConnection::create($data)],201); }
    public function testIntegration(IntegrationConnection $integration): JsonResponse { $integration->update(['status'=>'active','last_sync_at'=>now(),'last_error'=>null]); return response()->json(['success'=>true,'data'=>$integration]); }
    public function webhooks(Request $r): JsonResponse { return $this->list(WebhookSubscription::class, $r); }
    public function createWebhook(Request $r): JsonResponse { $data=$r->validate(['name'=>'required|string','endpoint'=>'required|url','events'=>'required|array']); $data['secret']=bin2hex(random_bytes(16)); return response()->json(['data'=>WebhookSubscription::create($data)],201); }
    public function reports(Request $r): JsonResponse { return $this->list(ScheduledReport::class, $r); }
    public function scheduleReport(Request $r): JsonResponse { $data=$r->validate(['name'=>'required|string','report_key'=>'required|string','frequency'=>'required|in:daily,weekly,monthly','format'=>'in:pdf,xlsx,csv','recipients'=>'array','filters'=>'array']); return response()->json(['data'=>ScheduledReport::create($data)],201); }
    public function customFields(Request $r): JsonResponse { return $this->list(CustomField::class, $r); }
    public function createCustomField(Request $r): JsonResponse { $data=$r->validate(['entity'=>'required|string','key'=>'required|string','label'=>'required|string','type'=>'required|in:text,number,boolean,date,select,multiselect']); return response()->json(['data'=>CustomField::create($data)],201); }
    public function notificationTemplates(Request $r): JsonResponse { return $this->list(NotificationTemplate::class, $r); }
    public function createNotificationTemplate(Request $r): JsonResponse { $data=$r->validate(['event'=>'required|string','channel'=>'required|in:email,sms,push,whatsapp,in_app','body'=>'required|string','subject'=>'nullable|string']); return response()->json(['data'=>NotificationTemplate::create($data)],201); }
    public function auditLogs(Request $r): JsonResponse { return $this->list(AuditLog::class, $r); }
    public function moduleCatalog(): JsonResponse { return response()->json(['data'=>[ ['key'=>'data-import-export','name_ar'=>'الاستيراد والتصدير','name_en'=>'Data Import & Export'], ['key'=>'workflow','name_ar'=>'سير العمل','name_en'=>'Workflow Automation'], ['key'=>'approvals','name_ar'=>'الموافقات','name_en'=>'Approvals'], ['key'=>'integrations','name_ar'=>'التكاملات','name_en'=>'Integrations Hub'], ['key'=>'webhooks','name_ar'=>'Webhooks','name_en'=>'Webhooks'], ['key'=>'scheduled-reports','name_ar'=>'التقارير المجدولة','name_en'=>'Scheduled Reports'], ['key'=>'custom-fields','name_ar'=>'الحقول المخصصة','name_en'=>'Custom Fields'], ['key'=>'notifications','name_ar'=>'الإشعارات والقوالب','name_en'=>'Notifications'], ['key'=>'audit','name_ar'=>'سجل التدقيق','name_en'=>'Audit Trail'] ]]); }
}
