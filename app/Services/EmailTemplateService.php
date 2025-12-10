<?php

namespace App\Services;

use App\Contracts\Communication\CommunicationMessage;
use App\Models\CommunicationTemplate;
use App\Models\Organization;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class EmailTemplateService
{
    /**
     * Render an email template with person data
     */
    public function renderTemplate(
        CommunicationTemplate $template,
        Person $person,
        array $extraVariables = []
    ): array {
        try {
            // Get the rendered template with variables
            $rendered = $template->render($person, $extraVariables);
            
            // If template uses a Blade view, render it with variables
            if ($this->isBladeTemplate($template->content)) {
                $viewName = $this->extractViewName($template->content);
                
                if (View::exists($viewName)) {
                    $variables = array_merge(
                        $this->getPersonVariables($person),
                        $extraVariables
                    );
                    
                    $rendered['content'] = view($viewName, $variables)->render();
                }
            }
            
            return $rendered;
        } catch (\Exception $e) {
            Log::error('Error rendering email template', [
                'template_id' => $template->id,
                'person_id' => $person->id,
                'error' => $e->getMessage()
            ]);
            
            throw new \RuntimeException('Failed to render email template: ' . $e->getMessage());
        }
    }

    /**
     * Create a CommunicationMessage from a template
     */
    public function createMessageFromTemplate(
        CommunicationTemplate $template,
        Person $person,
        string $channel = 'email',
        array $extraVariables = []
    ): CommunicationMessage {
        // Validate that template supports the channel
        if (!$template->supportsChannel($channel)) {
            throw new \InvalidArgumentException(
                "Template '{$template->name}' does not support channel '{$channel}'"
            );
        }

        $rendered = $this->renderTemplate($template, $person, $extraVariables);
        
        // Get recipient based on channel
        $recipient = $this->getRecipientForChannel($person, $channel);
        
        if (!$recipient) {
            throw new \RuntimeException("No valid {$channel} address found for person {$person->id}");
        }

        // Mark template as used
        $template->markAsUsed();

        return CommunicationMessage::create(
            channel: $channel,
            recipient: $recipient,
            content: $rendered['content'],
            options: [
                'subject' => $rendered['subject'],
                'template_id' => $template->id,
                'template_name' => $template->name,
                'variables_used' => $rendered['variables_used'] ?? []
            ]
        );
    }

    /**
     * Send bulk messages using a template
     */
    public function createBulkMessagesFromTemplate(
        CommunicationTemplate $template,
        Collection $persons,
        string $channel = 'email',
        array $extraVariables = []
    ): Collection {
        $messages = collect();

        foreach ($persons as $person) {
            try {
                $message = $this->createMessageFromTemplate(
                    $template,
                    $person,
                    $channel,
                    $extraVariables
                );
                
                $messages->push($message);
            } catch (\Exception $e) {
                Log::warning('Failed to create message for person', [
                    'template_id' => $template->id,
                    'person_id' => $person->id,
                    'channel' => $channel,
                    'error' => $e->getMessage()
                ]);
                
                // Continue with other persons even if one fails
                continue;
            }
        }

        return $messages;
    }

    /**
     * Get available templates for a user and organization
     */
    public function getAvailableTemplates(
        int $userId,
        int $OrganizationId,
        ?string $category = null,
        ?string $channel = null
    ): Collection {
        $query = CommunicationTemplate::active()
            ->accessibleBy($userId, $OrganizationId);

        if ($category) {
            $query->where('category', $category);
        }

        if ($channel) {
            $query->whereJsonContains('supported_channels', $channel);
        }

        return $query->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Preview template with sample data
     */
    public function previewTemplate(
        CommunicationTemplate $template,
        ?Person $samplePerson = null
    ): array {
        // Use sample person or create mock data
        if (!$samplePerson) {
            $samplePerson = $this->createMockPerson();
        }

        $sampleVariables = [
            'current_datetime' => now()->format('F j, Y \a\t g:i A'),
            'login_url' => url('/login'),
            'reset_url' => url('/password/reset/sample-token'),
            'action_url' => url('/dashboard'),
            'action_button_text' => 'Take Action',
        ];

        return $this->renderTemplate($template, $samplePerson, $sampleVariables);
    }

    /**
     * Validate template variables
     */
    public function validateTemplate(CommunicationTemplate $template): array
    {
        $errors = [];
        $warnings = [];

        // Check for required variables that might be missing
        $requiredVars = ['full_name', 'email_address', 'organization_name'];
        $templateVars = array_keys($template->variables ?? []);

        foreach ($requiredVars as $var) {
            if (!in_array($var, $templateVars)) {
                $warnings[] = "Missing recommended variable: {$var}";
            }
        }

        // Check for undefined variables in content
        $content = $template->content . ' ' . ($template->subject ?? '');
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $usedVar) {
                $usedVar = trim($usedVar);
                if (!in_array($usedVar, $templateVars)) {
                    $errors[] = "Undefined variable in template: {$usedVar}";
                }
            }
        }

        // Check channel compatibility
        if (empty($template->supported_channels)) {
            $errors[] = "Template must support at least one communication channel";
        }

        // Check content length for SMS
        if (in_array('sms', $template->supported_channels ?? [])) {
            $estimatedLength = strlen(preg_replace('/\{\{[^}]+\}\}/', 'X', $template->content));
            if ($estimatedLength > 160) {
                $warnings[] = "SMS content may exceed standard message length (160 characters)";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Get template usage statistics
     */
    public function getTemplateStats(CommunicationTemplate $template): array
    {
        return [
            'usage_count' => $template->usage_count,
            'last_used_at' => $template->last_used_at?->format('Y-m-d H:i:s'),
            'supported_channels' => $template->supported_channels,
            'variable_count' => count($template->variables ?? []),
            'is_shared' => $template->is_shared,
            'category' => $template->category,
            'created_at' => $template->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Clone a template for customization
     */
    public function cloneTemplate(
        CommunicationTemplate $originalTemplate,
        int $newUserId,
        ?int $newOrganizationId = null,
        array $modifications = []
    ): CommunicationTemplate {
        $data = $originalTemplate->toArray();
        
        // Remove fields that shouldn't be copied
        unset($data['id'], $data['created_at'], $data['updated_at']);
        
        // Set new ownership
        $data['user_id'] = $newUserId;
        $data['organization_id'] = $newOrganizationId;
        $data['usage_count'] = 0;
        $data['last_used_at'] = null;
        
        // Apply modifications
        $data = array_merge($data, $modifications);
        
        // Add "Copy" suffix if name wasn't changed
        if (!isset($modifications['name'])) {
            $data['name'] = $originalTemplate->name . ' (Copy)';
        }

        return CommunicationTemplate::create($data);
    }

    /**
     * Check if content references a Blade view
     */
    private function isBladeTemplate(string $content): bool
    {
        return preg_match('/^@extends\(/', $content) || 
               preg_match('/^emails\./', $content);
    }

    /**
     * Extract view name from template content
     */
    private function extractViewName(string $content): string
    {
        // If content starts with view name directly
        if (preg_match('/^emails\.[\w\.]+/', $content)) {
            return trim($content);
        }
        
        // If content has @extends directive
        if (preg_match('/^@extends\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
            return $matches[1];
        }
        
        return '';
    }

    /**
     * Get recipient address based on channel
     */
    private function getRecipientForChannel(Person $person, string $channel): ?string
    {
        return match($channel) {
            'email' => $person->primaryEmail()?->email,
            'sms', 'whatsapp' => $person->primaryPhone()?->number,
            default => null
        };
    }

    /**
     * Get person variables for template rendering
     */
    private function getPersonVariables(Person $person): array
    {
        $variables = [
            'given_name' => $person->given_name,
            'family_name' => $person->family_name,
            'full_name' => trim($person->given_name . ' ' . $person->family_name),
            'person_id' => $person->person_id,
            'gender' => $person->gender,
        ];

        // Add affiliation data
        $affiliation = $person->affiliations->first();
        if ($affiliation) {
            $variables['role_title'] = $affiliation->role_title;
            $variables['organization_name'] = $affiliation->Organization->legal_name ?? '';
        }

        // Add contact information
        $primaryPhone = $person->primaryPhone();
        if ($primaryPhone) {
            $variables['phone_number'] = $primaryPhone->number;
        }

        $primaryEmail = $person->primaryEmail();
        if ($primaryEmail) {
            $variables['email_address'] = $primaryEmail->email;
        }

        return $variables;
    }

    /**
     * Create mock person data for template preview
     */
    private function createMockPerson(): Person
    {
        $person = new Person();
        $person->given_name = 'John';
        $person->family_name = 'Doe';
        $person->person_id = 'SAMPLE001';
        $person->gender = 'M';

        // Mock affiliation
        $Organization = new Organization();
        $Organization->legal_name = 'Sample Organization Ltd.';
        
        // We'll simulate the relationships for preview
        $person->setRelation('affiliations', collect([(object)[
            'role_title' => 'Manager',
            'Organization' => $Organization
        ]]));

        return $person;
    }
}