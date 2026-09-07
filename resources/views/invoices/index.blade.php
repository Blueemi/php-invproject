<x-app-layout>
  <h1>{{ __('Sąskaitos') }}</h1>

  @include("components.search_buttons", ["createNew" => route("invoices.create"), "filter" => ""])

  <table>
    <tr>
      <th>{{ __('Dokumento Data') }}</th>
      <th>{{ __('Darbuotojas') }}</th>
      <th>{{ __('Apmokėjimo būsena') }}</th>
      <th class="w-1/8 text-right">{{ __('Veiksmai') }}</th>
    </tr>
    @foreach($items as $item)

    <tr data-tr="{{ __('Sąskaita') }} {{ $item->id }}">
      <td data-th="{{ __('Dokumento Data') }}">{{ $item->document_date }}</td>
      <td data-th="{{ __('Darbuotojas') }}">{{ $item->contrahent_name }}</td>
      <td data-th="{{ __('Apmokėjimo būsena') }}">
        <span>{{ $item->paid ? __('Apmokėta') : __('Neapmokėta') }}</span>
        <form action="{{ route('invoices.payment-status', $item) }}" method="POST">
          @csrf
          @method('PATCH')
          <input type="hidden" name="paid" value="{{ $item->paid ? '0' : '1' }}">
          <button type="submit" class="btn">
            {{ $item->paid ? __('Pažymėti kaip neapmokėtą') : __('Pažymėti kaip apmokėtą') }}
          </button>
        </form>
      </td>
      <td data-th="{{ __('Veiksmai') }}">
          @include("partials.actions", [
            'item' => $item,
            'editRoute' => "invoices.edit",
            'showRoute' => "invoices.read",
          ])

        </td>
    </tr>
    @endforeach
  </table>
</x-app-layout>
