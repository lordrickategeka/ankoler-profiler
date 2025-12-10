<?php

namespace Database\Seeders;

use App\Models\CommunicationTemplate;
use Illuminate\Database\Seeder;

class CommunicationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Welcome Email Template
            [
                'name' => 'User Welcome Email',
                'description' => 'Welcome email sent to new users when their account is created',
                'user_id' => 1, // Admin user
                'organization_id' => null, // Global template
                'subject' => 'Welcome to {{organization_name}}!',
                'content' => $this->getWelcomeEmailContent(),
                'category' => 'welcome',
                'supported_channels' => ['email'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'given_name' => 'User\'s first name',
                    'family_name' => 'User\'s last name',
                    'email_address' => 'User\'s email address',
                    'organization_name' => 'Organization name',
                    'role_title' => 'User\'s role/position',
                    'person_id' => 'Unique person identifier',
                    'temporary_password' => 'Temporary login password (if applicable)',
                    'login_url' => 'System login URL'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // Password Reset Template
            [
                'name' => 'Password Reset Request',
                'description' => 'Email sent when user requests password reset',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => 'Reset Your Password - {{organization_name}}',
                'content' => $this->getPasswordResetContent(),
                'category' => 'security',
                'supported_channels' => ['email'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'email_address' => 'User\'s email address',
                    'organization_name' => 'Organization name',
                    'reset_url' => 'Password reset link',
                    'current_datetime' => 'Current date and time'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // General Notification Template
            [
                'name' => 'System Notification',
                'description' => 'General purpose notification template for various system alerts',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => '{{notification_title}} - {{organization_name}}',
                'content' => $this->getNotificationContent(),
                'category' => 'notification',
                'supported_channels' => ['email', 'sms'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'organization_name' => 'Organization name',
                    'notification_title' => 'Notification title',
                    'notification_message' => 'Main notification message',
                    'notification_type' => 'Type of notification (info, warning, success, danger)',
                    'notification_details' => 'Additional notification details',
                    'action_required' => 'Whether action is required from user',
                    'action_instructions' => 'Instructions for required action',
                    'action_url' => 'URL for action button',
                    'action_button_text' => 'Text for action button',
                    'current_datetime' => 'Current date and time',
                    'reference_number' => 'Reference or ticket number'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // Reminder Template
            [
                'name' => 'Task Reminder',
                'description' => 'Reminder email for tasks, deadlines, or follow-ups',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => 'Reminder: {{reminder_title}} - {{organization_name}}',
                'content' => $this->getReminderContent(),
                'category' => 'reminder',
                'supported_channels' => ['email', 'sms'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'organization_name' => 'Organization name',
                    'role_title' => 'User\'s role/position',
                    'email_address' => 'User\'s email address',
                    'reminder_title' => 'Reminder title',
                    'reminder_subject' => 'What the reminder is about',
                    'reminder_message' => 'Detailed reminder message',
                    'due_date' => 'Due date for the task/event',
                    'priority' => 'Priority level of the reminder',
                    'action_url' => 'URL for action button',
                    'action_button_text' => 'Text for primary action button',
                    'secondary_action_url' => 'URL for secondary action',
                    'secondary_button_text' => 'Text for secondary button',
                    'checklist' => 'JSON array of checklist items'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // Meeting Invitation Template
            [
                'name' => 'Meeting Invitation',
                'description' => 'Template for meeting invitations and calendar events',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => 'Meeting Invitation: {{meeting_title}} - {{organization_name}}',
                'content' => $this->getMeetingInvitationContent(),
                'category' => 'meeting',
                'supported_channels' => ['email'],
                'variables' => [
                    'full_name' => 'Invitee\'s full name',
                    'organization_name' => 'Organization name',
                    'role_title' => 'Invitee\'s role/position',
                    'meeting_title' => 'Meeting title/subject',
                    'meeting_datetime' => 'Meeting date and time',
                    'meeting_location' => 'Physical meeting location',
                    'meeting_link' => 'Online meeting link (Zoom, Teams, etc.)',
                    'meeting_duration' => 'Expected meeting duration',
                    'organizer_name' => 'Name of meeting organizer',
                    'meeting_agenda' => 'Meeting agenda or description',
                    'preparation_notes' => 'What attendees should prepare',
                    'attendees_list' => 'List of other attendees',
                    'accept_url' => 'URL to accept invitation',
                    'decline_url' => 'URL to decline invitation'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // Security Alert Template
            [
                'name' => 'Security Alert',
                'description' => 'Security-related notifications and alerts',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => 'Security Alert - {{organization_name}}',
                'content' => $this->getSecurityAlertContent(),
                'category' => 'security',
                'supported_channels' => ['email', 'sms'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'organization_name' => 'Organization name',
                    'email_address' => 'User\'s email address',
                    'person_id' => 'Unique person identifier',
                    'activity_type' => 'Type of security activity detected',
                    'activity_datetime' => 'When the activity occurred',
                    'ip_address' => 'IP address of the activity',
                    'device_info' => 'Device information',
                    'location' => 'Geographic location (if available)',
                    'security_action_taken' => 'What security measures were applied',
                    'secure_account_url' => 'URL to secure account',
                    'contact_support_url' => 'URL to contact support'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // Data Export Template
            [
                'name' => 'Data Export Ready',
                'description' => 'Notification when data export is ready for download',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => 'Your Data Export is Ready - {{organization_name}}',
                'content' => $this->getDataExportContent(),
                'category' => 'system',
                'supported_channels' => ['email'],
                'variables' => [
                    'full_name' => 'User\'s full name',
                    'organization_name' => 'Organization name',
                    'email_address' => 'User\'s email address',
                    'export_type' => 'Type of data exported',
                    'file_format' => 'Export file format (CSV, Excel, etc.)',
                    'file_size' => 'Size of the export file',
                    'record_count' => 'Number of records in export',
                    'generation_datetime' => 'When the export was generated',
                    'export_filters' => 'Filters applied to the export',
                    'data_description' => 'Description of exported data',
                    'request_id' => 'Export request identifier',
                    'download_url' => 'URL to download the file',
                    'view_exports_url' => 'URL to view all exports'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            // SMS Templates
            [
                'name' => 'SMS Notification',
                'description' => 'Short notification via SMS',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => null, // SMS doesn't use subject
                'content' => 'Hello {{given_name}}, {{notification_message}} - {{organization_name}}. {{action_url}}',
                'category' => 'notification',
                'supported_channels' => ['sms'],
                'variables' => [
                    'given_name' => 'User\'s first name',
                    'organization_name' => 'Organization name (short)',
                    'notification_message' => 'Brief notification message',
                    'action_url' => 'Short URL for action (if needed)'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],

            [
                'name' => 'SMS Reminder',
                'description' => 'Brief reminder via SMS',
                'user_id' => 1,
                'organization_id' => null,
                'subject' => null,
                'content' => 'Reminder: {{reminder_subject}} due {{due_date}}. {{action_url}} - {{organization_name}}',
                'category' => 'reminder',
                'supported_channels' => ['sms'],
                'variables' => [
                    'organization_name' => 'Organization name (short)',
                    'reminder_subject' => 'Brief reminder subject',
                    'due_date' => 'Due date',
                    'action_url' => 'Short URL for action'
                ],
                'is_active' => true,
                'is_shared' => true,
                'usage_count' => 0,
            ],
        ];

        foreach ($templates as $template) {
            CommunicationTemplate::create($template);
        }
    }

    private function getWelcomeEmailContent(): string
    {
        return view('emails.communication.welcome')->render();
    }

    private function getPasswordResetContent(): string
    {
        return view('emails.communication.password-reset')->render();
    }

    private function getNotificationContent(): string
    {
        return view('emails.communication.notification')->render();
    }

    private function getReminderContent(): string
    {
        return view('emails.communication.reminder')->render();
    }

    private function getMeetingInvitationContent(): string
    {
        return view('emails.communication.meeting-invitation')->render();
    }

    private function getSecurityAlertContent(): string
    {
        return view('emails.communication.security-alert')->render();
    }

    private function getDataExportContent(): string
    {
        return view('emails.communication.data-export')->render();
    }
}