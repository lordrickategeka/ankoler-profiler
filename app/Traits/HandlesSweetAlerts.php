<?php

namespace App\Traits;

trait HandlesSweetAlerts
{
    /**
     * Show success alert
     */
    public function showSuccess($message, $title = 'Success!')
    {
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $title,
            'message' => $message,
            'timer' => 3000
        ]);
    }

    /**
     * Show error alert
     */
    public function showError($message, $title = 'Error!')
    {
        $this->dispatch('sweet-alert', [
            'type' => 'error',
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Show warning alert
     */
    public function showWarning($message, $title = 'Warning!')
    {
        $this->dispatch('sweet-alert', [
            'type' => 'warning',
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Show info alert
     */
    public function showInfo($message, $title = 'Information')
    {
        $this->dispatch('sweet-alert', [
            'type' => 'info',
            'title' => $title,
            'message' => $message,
            'timer' => 4000
        ]);
    }

    /**
     * Handle form errors with proper logging and user feedback
     */
    public function handleFormError(\Exception $exception, $context = 'Form Error', $userMessage = null)
    {
        $result = \handle_form_error($exception, $context, $userMessage);
        
        $this->showError($result['message']);
        
        return $result;
    }

    /**
     * Handle validation errors
     */
    public function handleValidationErrors(\Illuminate\Validation\ValidationException $exception, $context = 'Validation Error')
    {
        $result = \handle_validation_errors($exception, $context);
        
        $this->showWarning($result['message']);
        
        return $result;
    }

    /**
     * Handle successful operations
     */
    public function handleSuccess($message, $title = 'Success!')
    {
        $result = \handle_form_success($message, $title);
        
        $this->showSuccess($message, $title);
        
        return $result;
    }

    /**
     * Show confirmation dialog and return promise-like structure
     */
    public function showConfirmation($title = 'Are you sure?', $text = 'This action cannot be undone.', $confirmText = 'Yes, continue!')
    {
        $this->dispatch('sweet-confirm', [
            'title' => $title,
            'text' => $text,
            'confirmText' => $confirmText,
            'cancelText' => 'Cancel'
        ]);
    }

    /**
     * Show toast notification (less intrusive)
     */
    public function showToast($message, $type = 'success', $position = 'top-end')
    {
        $this->dispatch('sweet-toast', [
            'type' => $type,
            'message' => $message,
            'position' => $position,
            'timer' => 3000,
            'timerProgressBar' => true,
            'showConfirmButton' => false
        ]);
    }

    /**
     * Handle database constraint errors with user-friendly messages
     */
    public function handleDatabaseError(\Exception $exception, $operation = 'operation')
    {
        $message = $exception->getMessage();
        
        if (str_contains($message, 'Duplicate entry')) {
            return $this->handleFormError($exception, 'Duplicate Entry', 'This record already exists. Please check for duplicates.');
        }
        
        if (str_contains($message, 'foreign key constraint')) {
            return $this->handleFormError($exception, 'Foreign Key Constraint', 'Cannot delete this record as it is being used elsewhere.');
        }
        
        if (str_contains($message, 'Connection refused')) {
            return $this->handleFormError($exception, 'Database Connection', 'Database connection error. Please try again later.');
        }
        
        return $this->handleFormError($exception, 'Database Error', "An error occurred while performing the {$operation}. Please try again.");
    }

    /**
     * Handle file upload errors
     */
    public function handleFileError(\Exception $exception, $context = 'File Upload')
    {
        $message = $exception->getMessage();
        
        if (str_contains($message, 'size') || str_contains($message, 'large')) {
            return $this->handleFormError($exception, $context, 'File is too large. Please choose a smaller file.');
        }
        
        if (str_contains($message, 'type') || str_contains($message, 'format')) {
            return $this->handleFormError($exception, $context, 'Invalid file type. Please choose a supported file format.');
        }
        
        return $this->handleFormError($exception, $context, 'File upload error. Please try again.');
    }

    /**
     * Handle permission errors
     */
    public function handlePermissionError(\Exception $exception = null)
    {
        $message = 'You do not have permission to perform this action.';
        
        if ($exception) {
            $this->handleFormError($exception, 'Permission Error', $message);
        } else {
            $this->showError($message, 'Access Denied');
        }
    }
}