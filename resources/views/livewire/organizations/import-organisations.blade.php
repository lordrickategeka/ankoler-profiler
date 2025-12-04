<div class="ml-6 md:ml-12 lg:ml-16">
    <form wire:submit.prevent="import">
        <input type="file" wire:model="file" accept=".csv,.xlsx">
        @error('file') <span class="error">{{ $message }}</span> @enderror
        <button type="submit" class="btn btn-primary mt-2">Import Organisations</button>
    </form>
    <a href="{{ route('organizations.template') }}" class="btn btn-outline btn-secondary mt-2" target="_blank">
        Download Default Template
    </a>
    <div class="mt-4 flex flex-col md:flex-row gap-8">
        <!-- Existing Fields Column -->
        <div class="flex-1 bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h4 class="font-semibold mb-4">Existing Template Fields</h4>
            <div class="mb-4 flex gap-2">
                <button type="button" class="btn btn-sm btn-outline btn-info" onclick="selectAllFields(true)">Select All</button>
                <button type="button" class="btn btn-sm btn-outline btn-warning" onclick="selectAllFields(false)">Deselect All</button>
            </div>
            <ul class="list-none text-sm" id="fields-list">
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> category</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> legal_name</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> display_name</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> code</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> organization_type</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> registration_number</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> contact_email</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> contact_phone</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> date_established</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> address_line_1</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> city</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> country</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> primary_contact_name</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> primary_contact_email</label></li>
                <li><label><input type="checkbox" class="field-checkbox" checked onchange="updateSelectedFields()"> primary_contact_phone</label></li>
            </ul>
        </div>
        <!-- Add Custom Field Column -->
        <div class="flex-1 bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h4 class="font-semibold mb-4">Add Custom Field</h4>
            <form id="custom-field-form" onsubmit="addCustomField(event)">
                <div class="flex gap-2 mb-4">
                    <input type="text" id="custom-field-input" class="input input-bordered input-sm" placeholder="Custom field name" required>
                    <button type="submit" class="btn btn-sm btn-success">Add Field</button>
                </div>
            </form>
            <ul class="list-none text-sm" id="custom-fields-list"></ul>
        </div>
        <!-- Selected Fields Column -->
        <div class="flex-1 bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h4 class="font-semibold mb-4">Selected & Created Fields</h4>
            <ul class="list-none text-sm" id="selected-fields-list"></ul>
            <button type="button" class="btn btn-outline btn-primary mt-4 w-full" onclick="exportCustomTemplate()">Export Custom Template</button>
        </div>
        <script>
            function selectAllFields(select) {
                document.querySelectorAll('#fields-list .field-checkbox').forEach(cb => {
                    cb.checked = select;
                });
                updateSelectedFields();
            }
            function updateSelectedFields() {
                const selectedList = document.getElementById('selected-fields-list');
                selectedList.innerHTML = '';
                // Existing fields
                document.querySelectorAll('#fields-list .field-checkbox').forEach(cb => {
                    if (cb.checked) {
                        const label = cb.parentNode.textContent.trim();
                        const li = document.createElement('li');
                        li.textContent = label;
                        selectedList.appendChild(li);
                    }
                });
                // Custom fields
                document.querySelectorAll('#custom-fields-list input[type=checkbox]').forEach(cb => {
                    if (cb.checked) {
                        const label = cb.parentNode.textContent.trim();
                        const li = document.createElement('li');
                        li.textContent = label;
                        selectedList.appendChild(li);
                    }
                });
            }
            function addCustomField(e) {
                e.preventDefault();
                const input = document.getElementById('custom-field-input');
                const value = input.value.trim();
                if (value) {
                    const ul = document.getElementById('custom-fields-list');
                    const li = document.createElement('li');
                    li.innerHTML = `<label><input type='checkbox' checked onchange='updateSelectedFields()'> ${value}</label>`;
                    ul.appendChild(li);
                    input.value = '';
                    updateSelectedFields();
                }
            }
            function exportCustomTemplate() {
                // Gather selected fields
                const selected = [];
                document.querySelectorAll('#fields-list .field-checkbox').forEach(cb => {
                    if (cb.checked) selected.push(cb.parentNode.textContent.trim());
                });
                document.querySelectorAll('#custom-fields-list input[type=checkbox]').forEach(cb => {
                    if (cb.checked) selected.push(cb.parentNode.textContent.trim());
                });
                // Create a form and submit via POST to export route
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('organizations.export-template') }}";
                form.target = '_blank';
                // Add CSRF token
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);
                // Add fields
                selected.forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'fields[]';
                    input.value = field;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
            // Initial update
            document.addEventListener('DOMContentLoaded', updateSelectedFields);
        </script>
    </div>
    @if($message)
        <div class="alert alert-success mt-2">{{ $message }}</div>
    @endif
</div>
