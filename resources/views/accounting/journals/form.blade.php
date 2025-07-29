<form 
    action="{{ isset($journal) ? route('accounting.journals.update', $journal) : route('accounting.journals.store') }}" 
    method="POST" 
    enctype="multipart/form-data"
>
    @csrf
    @if(isset($journal))
        @method('PUT')
    @endif

    <!-- General Journal Fields -->
    <!-- Row: Date + Attachment -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Date</label>
            <input type="date" name="date" class="form-control" 
                value="{{ old('date', $journal->date ?? now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label>Attachment</label>
            <input type="file" name="attachment" class="form-control">
            @if(isset($journal) && $journal->attachment)
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $journal->attachment) }}" target="_blank">View Current Attachment</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Row: Description (Full Width) -->
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control">{{ old('description', $journal->description ?? '') }}</textarea>
    </div>


    <!-- Journal Items Table -->
    <hr>
    <h5>Journal Entries (Debit / Credit)</h5>

    <table class="table table-bordered" id="items-table">
        <thead>
            <tr>
                <th>Account</th>
                <th>Nature</th>
                <th>Amount</th>
                <th>Description</th>
                <th><button type="button" class="btn btn-sm btn-success" onclick="addItem()">+</button></th>
            </tr>
        </thead>
        <tbody>
            @php $items = old('items', isset($journal) ? $journal->items->toArray() : []); @endphp
            @foreach($items as $index => $item)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][account_id]" class="form-control account-select">
                            <option value="">-- Select Account --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ $item['account_id'] == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][nature]" class="form-control">
                            <option value="debit" {{ $item['nature'] == 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="credit" {{ $item['nature'] == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[{{ $index }}][amount]" class="form-control"
                               value="{{ $item['amount'] }}">
                    </td>
                    <td>
                        <input type="text" name="items[{{ $index }}][description]" class="form-control"
                               value="{{ $item['description'] ?? '' }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">x</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Submit -->
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            {{ isset($journal) ? 'Update' : 'Create' }} Journal
        </button>
    </div>
</form>

<!-- Include jQuery and Select2 CSS/JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Dynamic Item JS -->
<script>
    let itemIndex = {{ count($items) }};

    function initSelect2() {
        $('select.account-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                width: '100%',
                placeholder: '-- Select Account --',
                allowClear: true
            });
        });
    }

    $(document).ready(function() {
        initSelect2();

        // If you want to re-initialize after any DOM change, use MutationObserver (optional)
        // const observer = new MutationObserver(initSelect2);
        // observer.observe(document.getElementById('items-table').getElementsByTagName('tbody')[0], { childList: true });
    });

    function addItem() {
        const row = `
        <tr>
            <td>
                <select name="items[${itemIndex}][account_id]" class="form-control account-select">
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="items[${itemIndex}][nature]" class="form-control">
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][amount]" class="form-control" />
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control" />
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">x</button>
            </td>
        </tr>
        `;
        $('#items-table tbody').append(row);
        itemIndex++;
        // Initialize Select2 only for new selects
        $('#items-table tbody tr:last select.account-select').select2({
            width: '100%',
            placeholder: '-- Select Account --',
            allowClear: true
        });
    }

    function removeItem(button) {
        button.closest('tr').remove();
        // Re-initialize Select2 in case of DOM changes
        initSelect2();
    }
</script>
