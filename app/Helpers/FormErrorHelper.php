<?php
// app/Helpers/FormErrorHelper.php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

if (!function_exists('handle_form_error')) {
    /**
     * Handle form errors with SweetAlert notifications
     * 
     * @param \Exception $exception
     * @param string $context
     * @param string $userMessage
     * @param bool $logError
     * @return array
     */
    function handle_form_error($exception, $context = 'Form Error', $userMessage = null, $logError = true)
    {
        // Log the error for debugging
        if ($logError) {
            Log::error($context . ': ' . $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'user_id' => auth()->id(),
                'url' => request()->url(),
                'ip' => request()->ip(),
            ]);
        }

        // Determine user-friendly message
        $displayMessage = $userMessage ?? get_user_friendly_message($exception);

        // Set session flash for SweetAlert
        Session::flash('error', [
            'title' => 'Error',
            'message' => $displayMessage,
            'type' => 'error'
        ]);

        return [
            'success' => false,
            'message' => $displayMessage,
            'error_type' => get_class($exception)
        ];
    }
}

if (!function_exists('handle_validation_errors')) {
    /**
     * Handle validation errors with SweetAlert
     * 
     * @param \Illuminate\Validation\ValidationException $exception
     * @param string $context
     * @return array
     */
    function handle_validation_errors($exception, $context = 'Validation Error')
    {
        $errors = $exception->errors();
        $firstError = collect($errors)->flatten()->first();
        
        // Log validation errors
        Log::warning($context . ': Validation failed', [
            'errors' => $errors,
            'user_id' => auth()->id(),
            'url' => request()->url(),
        ]);

        // Set session flash for SweetAlert
        Session::flash('error', [
            'title' => 'Validation Error',
            'message' => $firstError ?? 'Please check your input and try again.',
            'type' => 'warning'
        ]);

        return [
            'success' => false,
            'message' => $firstError,
            'errors' => $errors
        ];
    }
}

if (!function_exists('handle_form_success')) {
    /**
     * Handle successful form submissions with SweetAlert
     * 
     * @param string $message
     * @param string $title
     * @param array $data
     * @return array
     */
    function handle_form_success($message, $title = 'Success', $data = [])
    {
        // Set session flash for SweetAlert
        Session::flash('success', [
            'title' => $title,
            'message' => $message,
            'type' => 'success'
        ]);

        return array_merge([
            'success' => true,
            'message' => $message
        ], $data);
    }
}

if (!function_exists('handle_form_warning')) {
    /**
     * Handle form warnings with SweetAlert
     * 
     * @param string $message
     * @param string $title
     * @return array
     */
    function handle_form_warning($message, $title = 'Warning')
    {
        // Set session flash for SweetAlert
        Session::flash('warning', [
            'title' => $title,
            'message' => $message,
            'type' => 'warning'
        ]);

        return [
            'success' => false,
            'message' => $message,
            'type' => 'warning'
        ];
    }
}

if (!function_exists('handle_form_info')) {
    /**
     * Handle form info messages with SweetAlert
     * 
     * @param string $message
     * @param string $title
     * @return array
     */
    function handle_form_info($message, $title = 'Information')
    {
        // Set session flash for SweetAlert
        Session::flash('info', [
            'title' => $title,
            'message' => $message,
            'type' => 'info'
        ]);

        return [
            'success' => true,
            'message' => $message,
            'type' => 'info'
        ];
    }
}

if (!function_exists('get_user_friendly_message')) {
    /**
     * Convert technical error messages to user-friendly ones
     * 
     * @param \Exception $exception
     * @return string
     */
    function get_user_friendly_message($exception)
    {
        $message = $exception->getMessage();
        $exceptionType = get_class($exception);

        // Database-related errors
        if (str_contains($message, 'SQLSTATE') || str_contains($exceptionType, 'Database')) {
            if (str_contains($message, 'Duplicate entry')) {
                return 'This record already exists. Please check for duplicates.';
            }
            if (str_contains($message, 'foreign key constraint')) {
                return 'Cannot delete this record as it is being used elsewhere.';
            }
            if (str_contains($message, 'Connection refused')) {
                return 'Database connection error. Please try again later.';
            }
            return 'A database error occurred. Please try again.';
        }

        // File/Upload errors
        if (str_contains($exceptionType, 'File') || str_contains($message, 'file')) {
            if (str_contains($message, 'size')) {
                return 'File is too large. Please choose a smaller file.';
            }
            if (str_contains($message, 'type')) {
                return 'Invalid file type. Please choose a supported file format.';
            }
            return 'File upload error. Please try again.';
        }

        // Permission errors
        if (str_contains($exceptionType, 'Unauthorized') || str_contains($message, 'permission')) {
            return 'You do not have permission to perform this action.';
        }

        // Network/API errors
        if (str_contains($exceptionType, 'Http') || str_contains($message, 'curl')) {
            return 'Network error. Please check your connection and try again.';
        }

        // Validation errors (though these should use handle_validation_errors)
        if (str_contains($exceptionType, 'Validation')) {
            return 'Please check your input and try again.';
        }

        // Generic errors - clean up technical details
        $userMessage = preg_replace('/\s*\(SQL:.*?\)/', '', $message);
        $userMessage = preg_replace('/\s*in\s+\/.*?\.php.*?line\s+\d+/', '', $userMessage);
        
        // If message is still too technical, provide generic message
        if (strlen($userMessage) > 200 || str_contains($userMessage, 'Class') || str_contains($userMessage, '::')) {
            return 'An error occurred while processing your request. Please try again.';
        }

        return $userMessage;
    }
}

if (!function_exists('flash_sweet_alert')) {
    /**
     * Flash a SweetAlert message to session
     * 
     * @param string $type (success, error, warning, info)
     * @param string $message
     * @param string $title
     * @return void
     */
    function flash_sweet_alert($type, $message, $title = null)
    {
        $titles = [
            'success' => 'Success!',
            'error' => 'Error!',
            'warning' => 'Warning!',
            'info' => 'Information'
        ];

        Session::flash($type, [
            'title' => $title ?? $titles[$type] ?? 'Notification',
            'message' => $message,
            'type' => $type
        ]);
    }
}

if (!function_exists('dispatch_livewire_alert')) {
    /**
     * Dispatch SweetAlert to Livewire component
     * 
     * @param object $component (Livewire component instance)
     * @param string $type
     * @param string $message
     * @param string $title
     * @return void
     */
    function dispatch_livewire_alert($component, $type, $message, $title = null)
    {
        $titles = [
            'success' => 'Success!',
            'error' => 'Error!',
            'warning' => 'Warning!',
            'info' => 'Information'
        ];

        $component->dispatch('sweet-alert', [
            'type' => $type,
            'title' => $title ?? $titles[$type] ?? 'Notification',
            'message' => $message
        ]);
    }
}