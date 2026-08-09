<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Reports\FinancialReports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * الدورة المالية end-to-end: إصدار فاتورة ← تحصيل ← أثرها في التقارير.
 * الفكرة إن التقارير محسوبة من دفتر الأستاذ، فأي كسر في القيود بيظهر هنا.
 */
class FinancialCycleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ChartOfAccountsSeeder']);

        $this->user = User::create([
            'name' => 'Accountant',
            'email' => 'acc@test.local',
            'password' => 'password123',
            'role' => 'accountant',
        ]);
    }

    private function customer(): Customer
    {
        return Customer::create([
            'customer_code' => 'C-001',
            'name' => 'عميل تجريبي',
            'is_active' => true,
        ]);
    }

    private function issuedInvoice(float $unitPrice = 10000): Invoice
    {
        $customer = $this->customer();

        $create = $this->actingAs($this->user)->postJson('/api/v1/e-invoicing/invoices', [
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                ['item_name' => 'خدمة استشارية', 'quantity' => 1, 'unit_price' => $unitPrice],
            ],
        ]);

        $create->assertCreated();
        $invoiceId = $create->json('id');

        $this->actingAs($this->user)
            ->postJson("/api/v1/e-invoicing/invoices/{$invoiceId}/issue")
            ->assertOk();

        return Invoice::findOrFail($invoiceId);
    }

    public function test_issuing_an_invoice_creates_a_balanced_posted_entry(): void
    {
        $invoice = $this->issuedInvoice();

        $this->assertSame('issued', $invoice->status);
        $this->assertNotNull($invoice->journal_entry_id);
        $this->assertNotNull($invoice->qr_code);
        $this->assertEqualsWithDelta(11500.0, (float) $invoice->total_amount, 0.01);

        $entry = $invoice->journalEntry;
        $this->assertSame('posted', $entry->status);
        $this->assertTrue($entry->isBalanced());
    }

    public function test_receipt_reduces_the_invoice_balance_and_closes_it_when_fully_paid(): void
    {
        $invoice = $this->issuedInvoice();

        // تحصيل جزئي
        $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 5000,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 5000]],
        ])->assertCreated();

        $invoice->refresh();
        $this->assertSame('partial', $invoice->payment_status);
        $this->assertEqualsWithDelta(6500.0, $invoice->balance_due, 0.01);
        $this->assertSame('issued', $invoice->status);

        // تحصيل المتبقي
        $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 6500,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 6500]],
        ])->assertCreated();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->payment_status);
        $this->assertSame('paid', $invoice->status);
        $this->assertEqualsWithDelta(0.0, $invoice->balance_due, 0.01);
    }

    public function test_allocation_cannot_exceed_the_invoice_balance(): void
    {
        $invoice = $this->issuedInvoice();

        $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 99999,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 99999]],
        ])->assertStatus(422);
    }

    public function test_allocations_cannot_exceed_the_payment_amount(): void
    {
        $invoice = $this->issuedInvoice();

        $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 1000,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 5000]],
        ])->assertStatus(422);
    }

    public function test_voiding_a_receipt_restores_the_invoice_balance(): void
    {
        $invoice = $this->issuedInvoice();

        $payment = $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 5000,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 5000]],
        ])->assertCreated()->json();

        $this->actingAs($this->user)
            ->postJson("/api/v1/finance/payments/{$payment['id']}/void")
            ->assertOk();

        $invoice->refresh();
        $this->assertEqualsWithDelta(11500.0, $invoice->balance_due, 0.01);
        $this->assertSame('unpaid', $invoice->payment_status);

        // القيد الأصلي بيتعلّم reversed مش بيتحذف — مسار التدقيق محفوظ
        $this->assertDatabaseHas('journal_entries', [
            'entry_number' => 'JE-'.$payment['payment_number'],
            'status' => 'reversed',
        ]);
    }

    public function test_trial_balance_stays_balanced_across_the_whole_cycle(): void
    {
        $invoice = $this->issuedInvoice();

        $this->actingAs($this->user)->postJson('/api/v1/finance/payments', [
            'type' => 'receipt',
            'customer_id' => $invoice->customer_id,
            'payment_date' => now()->toDateString(),
            'amount' => 5000,
            'allocations' => [['target_id' => $invoice->id, 'amount' => 5000]],
        ])->assertCreated();

        $trialBalance = FinancialReports::trialBalance(null, now()->toDateString());
        $this->assertTrue($trialBalance['totals']['is_balanced']);

        $balanceSheet = FinancialReports::balanceSheet(now()->toDateString());
        $this->assertTrue($balanceSheet['check']['is_balanced']);
    }

    public function test_a_draft_invoice_cannot_be_issued_twice(): void
    {
        $invoice = $this->issuedInvoice();

        $this->actingAs($this->user)
            ->postJson("/api/v1/e-invoicing/invoices/{$invoice->id}/issue")
            ->assertStatus(422);
    }
}
