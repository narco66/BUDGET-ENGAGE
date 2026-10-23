<table class="pdf-data">
    <thead><tr>@foreach ($report['columns'] as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
    <tbody>
        @forelse ($report['rows'] as $row)
            <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($report['columns']) }}">{{ __('Aucune donnée.') }}</td></tr>
        @endforelse
        @if ($report['total'] !== null)
            <tr class="total"><td colspan="{{ max(1, count($report['columns']) - 1) }}">{{ __('TOTAL') }}</td><td>{{ $report['total'] }}</td></tr>
        @endif
    </tbody>
</table>
