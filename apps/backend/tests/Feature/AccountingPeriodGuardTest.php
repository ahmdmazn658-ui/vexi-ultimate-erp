<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * حماية الفترات المحاسبية المقفلة — JournalEntryObserver.
 *
 * الحماية دي مركزية، فالاختبارات هنا بتغطي السلوك اللي كل مسارات
 * الترحيل في النظام بتعتمد عليه.
 */
class AccountingPeriodGuardTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $code = '1110'): Account
    {
        return Account::create([
            'account_code' => $code,
            'account_name' => "Account {$code}",
            'account_type' => 'asset',
            'is_active' => true,
        ]);
    }

    private function closedPeriod(string $start, string $end): AccountingPeriod
    {
        return AccountingPeriod::create([
            'name' => substr($start, 0, 7),
            'fiscal_year' => (int) substr($start, 0, 4),
            'period_number' => (int) substr($start, 5, 2),
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'closed',
        ]);
    }

    public function test_posted_entry_is_blocked_inside_a_closed_period(): void
    {
        $this->closedPeriod('2026-03-01', '2026-03-31');

        $this->expectException(ValidationException::class);

        JournalEntry::create([
            'entry_number' => 'JE-TEST-1',
            'entry_date' => '2026-03-15',
            'status' => 'posted',
        ]);
    }

    public function test_draft_entry_is_allowed_inside_a_closed_period(): void
    {
        $this->closedPeriod('2026-03-01', '2026-03-31');

        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-2',
            'entry_date' => '2026-03-15',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'status' => 'draft']);
    }

    public function test_posted_entry_is_allowed_in_an_open_period(): void
    {
        AccountingPeriod::create([
            'name' => '2026-04',
            'fiscal_year' => 2026,
            'period_number' => 4,
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'status' => 'open',
        ]);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-3',
            'entry_date' => '2026-04-15',
            'status' => 'posted',
        ]);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'status' => 'posted']);
    }

    public function test_dates_outside_any_defined_period_are_treated_as_open(): void
    {
        $this->closedPeriod('2026-03-01', '2026-03-31');

        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-4',
            'entry_date' => '2025-11-20',
            'status' => 'posted',
        ]);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
    }

    public function test_period_boundaries_are_inclusive(): void
    {
        $this->closedPeriod('2026-03-01', '2026-03-31');

        // أول يوم في الفترة مقفول
        $this->assertTrue(AccountingPeriod::isDateClosed('2026-03-01'));
        // آخر يوم في الفترة مقفول
        $this->assertTrue(AccountingPeriod::isDateClosed('2026-03-31'));
        // اليوم اللي بعده خارج الفترة
        $this->assertFalse(AccountingPeriod::isDateClosed('2026-04-01'));
    }

    public function test_posted_entry_in_closed_period_cannot_be_deleted(): void
    {
        $entry = JournalEntry::create([
            'entry_number' => 'JE-TEST-5',
            'entry_date' => '2026-03-15',
            'status' => 'posted',
        ]);

        // نقفل الفترة بعد ما القيد اترحّل
        $this->closedPeriod('2026-03-01', '2026-03-31');

        $this->expectException(ValidationException::class);
        $entry->delete();
    }

    public function test_without_guard_allows_the_year_end_closing_entry(): void
    {
        $this->closedPeriod('2026-12-01', '2026-12-31');
        $this->account();

        $entry = AccountingPeriod::withoutGuard(fn () => JournalEntry::create([
            'entry_number' => 'JE-CLOSING-2026',
            'entry_date' => '2026-12-31',
            'status' => 'posted',
        ]));

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);

        // والحماية بترجع تشتغل بعدها مباشرة
        $this->assertTrue(AccountingPeriod::isDateClosed('2026-12-31'));
    }

    public function test_generate_for_year_creates_twelve_periods_without_duplicates(): void
    {
        $this->assertSame(12, AccountingPeriod::generateForYear(2027));
        $this->assertSame(0, AccountingPeriod::generateForYear(2027));
        $this->assertSame(12, AccountingPeriod::where('fiscal_year', 2027)->count());
    }
}
