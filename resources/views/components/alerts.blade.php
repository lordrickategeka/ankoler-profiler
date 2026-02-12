@props(['success' => session('success'), 'error' => session('error'), 'errorReason' => session('error_reason'), 'info' => session('info')])

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SweetAlert2 Alert Component -->
@if ($success)
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: '{{ $success }}',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
@endif

@if ($error)
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: '{{ $error }}',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
@endif

@if ($info)
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'info',
            title: '{{ $info }}',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
@endif
