# Form Error Helper Documentation

## Overview
The Form Error Helper provides a comprehensive error handling system with SweetAlert integration for consistent user feedback across all forms in the application.

## Files Created
- `app/Helpers/FormErrorHelper.php` - Main helper functions
- `app/Traits/HandlesSweetAlerts.php` - Livewire trait for easy integration
- `resources/views/components/sweet-alerts.blade.php` - Frontend SweetAlert component

## Installation

1. **Include in your layout** (e.g., `layouts/app.blade.php`):
```blade
<x-sweet-alerts />
```

2. **Use the trait in Livewire components**:
```php
use App\Traits\HandlesSweetAlerts;

class YourComponent extends Component
{
    use HandlesSweetAlerts;
    
    // Your component code...
}
```

## Usage Examples

### 1. In Livewire Components

#### Basic Success/Error Messages
```php
public function saveRecord()
{
    try {
        // Your save logic here
        $this->handleSuccess('Record saved successfully!');
        
    } catch (\Exception $e) {
        $this->handleFormError($e, 'Save Record', 'Failed to save the record.');
    }
}
```

#### Validation Errors
```php
public function validateAndSave()
{
    try {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users'
        ]);
        
        // Save logic...
        $this->showSuccess('User created successfully!');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->handleValidationErrors($e, 'User Creation');
    }
}
```

#### Database Errors
```php
public function deleteRecord($id)
{
    try {
        User::findOrFail($id)->delete();
        $this->showSuccess('Record deleted successfully!');
        
    } catch (\Exception $e) {
        $this->handleDatabaseError($e, 'delete');
    }
}
```

#### File Upload Errors
```php
public function uploadFile()
{
    try {
        // File upload logic
        $this->showSuccess('File uploaded successfully!');
        
    } catch (\Exception $e) {
        $this->handleFileError($e, 'File Upload');
    }
}
```

#### Permission Errors
```php
public function restrictedAction()
{
    if (!auth()->user()->can('perform_action')) {
        $this->handlePermissionError();
        return;
    }
    
    // Continue with action...
}
```

#### Toast Notifications (less intrusive)
```php
public function quickSave()
{
    // Save logic...
    $this->showToast('Auto-saved', 'success');
}
```

### 2. In Regular Controllers

#### Using Helper Functions
```php
public function store(Request $request)
{
    try {
        $user = User::create($request->validated());
        return handle_form_success('User created successfully!');
        
    } catch (\Exception $e) {
        return handle_form_error($e, 'User Creation');
    }
}
```

#### Flash Messages for Redirects
```php
public function update(Request $request, User $user)
{
    try {
        $user->update($request->validated());
        flash_sweet_alert('success', 'User updated successfully!');
        return redirect()->back();
        
    } catch (\Exception $e) {
        handle_form_error($e, 'User Update');
        return redirect()->back();
    }
}
```

### 3. Frontend JavaScript

#### Direct SweetAlert Calls
```javascript
// Success message
showSuccessAlert('Operation completed successfully!');

// Error message
showErrorAlert('Something went wrong!');

// Warning message
showWarningAlert('Please check your input.');

// Info message
showInfoAlert('Here is some information.');

// Validation errors
showValidationErrors({
    name: ['Name is required'],
    email: ['Email must be valid']
});
```

#### Confirmation Dialogs
```javascript
showConfirmDialog({
    title: 'Delete Record?',
    text: 'This action cannot be undone.',
    confirmButtonText: 'Yes, delete it!'
}).then((result) => {
    if (result.isConfirmed) {
        // Proceed with deletion
        @this.call('deleteRecord', recordId);
    }
});
```

## Available Helper Functions

### Global Functions (available everywhere)
- `handle_form_error($exception, $context, $userMessage, $logError)`
- `handle_validation_errors($exception, $context)`
- `handle_form_success($message, $title, $data)`
- `handle_form_warning($message, $title)`
- `handle_form_info($message, $title)`
- `flash_sweet_alert($type, $message, $title)`
- `dispatch_livewire_alert($component, $type, $message, $title)`

### Trait Methods (Livewire components)
- `showSuccess($message, $title)`
- `showError($message, $title)`
- `showWarning($message, $title)`
- `showInfo($message, $title)`
- `showToast($message, $type, $position)`
- `handleFormError($exception, $context, $userMessage)`
- `handleValidationErrors($exception, $context)`
- `handleSuccess($message, $title)`
- `handleDatabaseError($exception, $operation)`
- `handleFileError($exception, $context)`
- `handlePermissionError($exception)`

### JavaScript Functions (frontend)
- `showSuccessAlert(message, title)`
- `showErrorAlert(message, title)`
- `showWarningAlert(message, title)`
- `showInfoAlert(message, title)`
- `showValidationErrors(errors)`
- `showConfirmDialog(options)`

## Error Types Handled

### Database Errors
- Duplicate entry errors
- Foreign key constraint violations
- Connection issues
- General database errors

### File Upload Errors
- File size too large
- Invalid file type
- Upload failures

### Validation Errors
- Form validation failures
- Input format errors

### Permission Errors
- Unauthorized access attempts
- Insufficient permissions

### Network Errors
- API call failures
- Connection timeouts

## Customization

### Custom Error Messages
```php
// Override default error message
$this->handleFormError($e, 'Custom Context', 'Your custom user message');

// Let the helper determine the message
$this->handleFormError($e, 'Custom Context');
```

### Custom SweetAlert Styling
Edit `resources/views/components/sweet-alerts.blade.php` to customize:
- Colors
- Fonts
- Animation
- Positioning

### Custom Error Message Mapping
Edit the `get_user_friendly_message()` function in `FormErrorHelper.php` to add more error mappings.

## Best Practices

1. **Always use try-catch blocks** for operations that might fail
2. **Provide context** in error messages for better debugging
3. **Use appropriate message types** (success, error, warning, info)
4. **Keep user messages friendly** and actionable
5. **Log technical errors** for debugging while showing user-friendly messages
6. **Use toast notifications** for non-critical updates
7. **Use confirmation dialogs** for destructive actions

## Integration with Existing Forms

To integrate with existing forms, simply:

1. Add the trait to your Livewire component:
```php
use App\Traits\HandlesSweetAlerts;
```

2. Replace existing error handling:
```php
// Before
Log::error('Error: ' . $e->getMessage());
session()->flash('error', 'Something went wrong');

// After
$this->handleFormError($e, 'Operation Context');
```

3. Include the SweetAlert component in your layout:
```blade
<x-sweet-alerts />
```

That's it! Your forms now have consistent, user-friendly error handling with proper logging and SweetAlert notifications.