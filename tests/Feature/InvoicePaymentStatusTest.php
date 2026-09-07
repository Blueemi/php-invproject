<?php

use App\Enums\InvoiceStatusEnum;
use App\Models\Config;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $user = User::factory()->create();
    $this->tenant = Tenant::create([
        'id' => 'invoice-payment-'.Str::uuid(),
        'email' => 'invoice-payment@example.test',
    ]);
    $this->tenant->createDomain('localhost');
    tenancy()->initialize($this->tenant);

    Config::factory()->create();
    $this->actingAs($user)->withoutVite();

    $this->invoice = Invoice::create([
        'invoice_number' => '1',
        'invoice_series' => 'INV',
        'invoice_currency' => 'EUR',
        'document_date' => '2024-09-08',
        'invoice_total' => 100,
        'invoice_vat' => 21,
        'invoice_total_with_vat' => 121,
        'contrahent_name' => 'Example customer',
    ]);
});

afterEach(function () {
    tenancy()->end();
    $this->tenant->delete();
});

test('an invoice can be marked paid and then unpaid without changing its details', function () {
    $original = $this->invoice->fresh()->getAttributes();

    foreach (['1' => InvoiceStatusEnum::Paid, '0' => InvoiceStatusEnum::Unpaid] as $paid => $status) {
        $this->patch(route('invoices.payment-status', $this->invoice), ['paid' => (string) $paid])
            ->assertRedirect(route('invoices.index'));

        $invoice = $this->invoice->fresh();
        expect($invoice->paid)->toBe((bool) $paid)
            ->and($invoice->status)->toBe($status->value)
            ->and($invoice->getAttributes())->toMatchArray(
                array_diff_key($original, array_flip(['paid', 'status', 'updated_at']))
            );
    }
});

test('repeating a paid request leaves the invoice paid', function () {
    for ($attempt = 0; $attempt < 2; $attempt++) {
        $this->patch(route('invoices.payment-status', $this->invoice), ['paid' => '1'])
            ->assertRedirect(route('invoices.index'));
    }

    expect($this->invoice->fresh()->paid)->toBeTrue()
        ->and($this->invoice->fresh()->status)->toBe(InvoiceStatusEnum::Paid->value);
});

test('payment status requires a boolean value', function (array $input) {
    $this->from(route('invoices.index'))
        ->patch(route('invoices.payment-status', $this->invoice), $input)
        ->assertRedirect(route('invoices.index'))
        ->assertSessionHasErrors('paid');

    expect($this->invoice->fresh()->paid)->toBeFalse()
        ->and($this->invoice->fresh()->status)->toBe(InvoiceStatusEnum::Unpaid->value);
})->with([
    'missing' => [[]],
    'invalid' => [['paid' => 'unknown']],
]);

test('the invoice list shows the payment status and the next action', function () {
    $this->get(route('invoices.index'))
        ->assertOk()
        ->assertSeeText('Unpaid')
        ->assertSeeText('Mark as paid')
        ->assertSee('name="paid" value="1"', false);

    $this->patch(route('invoices.payment-status', $this->invoice), ['paid' => '1']);

    $this->get(route('invoices.index'))
        ->assertOk()
        ->assertSeeText('Paid')
        ->assertSeeText('Mark as unpaid')
        ->assertSee('name="paid" value="0"', false);
});
