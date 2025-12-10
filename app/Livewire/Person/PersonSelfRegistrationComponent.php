<?php

namespace App\Livewire\Person;

use App\Models\EmailAddress;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Person;
use App\Models\Organization;
use App\Models\PersonAffiliation;
use App\Models\PersonIdentifier;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PersonSelfRegistrationComponent extends Component
{
    // Step navigation removed
    use WithFileUploads;

    // Step navigation removed
    public $form = [
        'given_name' => '',
        'middle_name' => '',
        'family_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'country' => 'Uganda',
        'district' => '',
        'city' => '',
        'role_type' => 'STAFF',
        'role_title' => '',
        'organization_id' => '',
    ];

    // Documents step removed
    public $availableOrganizations = [];

    public function mount()
    {
        $this->availableOrganizations = Organization::all();
    }

    // Document add/remove methods removed

    // Step navigation methods removed

    public function submit()
    {
        try {
            $this->validate([
                'form.given_name' => 'required|string|max:255',
                'form.family_name' => 'required|string|max:255',
                'form.date_of_birth' => 'required|date',
                'form.gender' => ['required', Rule::in(['Male', 'Female'])],
                'form.phone' => 'required|string|max:20|unique:phones,number',
                'form.email' => 'required|email|unique:users,email',
                'form.address' => 'required|string',
                'form.country' => 'required|string',
                'form.district' => 'required|string',
                'form.city' => 'required|string',
                'form.role_title' => 'required|string',
                'form.organization_id' => 'required|exists:Organizations,id',
            ]);

            // Check if email exists and is not verified
            $existingUser = User::where('email', $this->form['email'])->first();
            if ($existingUser) {
                if (!$existingUser->hasVerifiedEmail()) {
                    $existingUser->sendEmailVerificationNotification();
                    session()->flash('info', 'This email is already registered but not verified. Verification email sent.');
                    return;
                } else {
                    session()->flash('error', 'Email address already exists and is verified.');
                    return;
                }
            }

            $temporaryPassword = Str::random(10);
            $Organization = Organization::find($this->form['organization_id']);
            $user = null;
            DB::beginTransaction();
            try {
                // Create User first
                $user = User::create([
                    'name' => $this->form['given_name'] . ' ' . $this->form['family_name'],
                    'email' => $this->form['email'],
                    'password' => bcrypt($temporaryPassword),
                ]);
                Log::info('User created', ['user_id' => $user->id]);

                // Create Person with user_id
                $person = Person::create([
                    'person_id' => \App\Helpers\IdGenerator::generatePersonId(),
                    'global_identifier' => \App\Helpers\IdGenerator::generateGlobalIdentifier(),
                    'organization_id' => $this->form['organization_id'],
                    'given_name' => $this->form['given_name'],
                    'middle_name' => $this->form['middle_name'],
                    'family_name' => $this->form['family_name'],
                    'date_of_birth' => $this->form['date_of_birth'],
                    'gender' => $this->form['gender'],
                    'country' => $this->form['country'],
                    'district' => $this->form['district'],
                    'address' => $this->form['address'],
                    'city' => $this->form['city'],
                    'user_id' => $user->id,
                    'classification' => json_encode(['STAFF']),
                    'created_by' => $user->id,
                ]);
                Log::info('Person created', ['person_id' => $person->id]);

                $this->createContactInformation($person);
                Log::info('Contact information created', ['person_id' => $person->id]);

                PersonAffiliation::create([
                    'person_id' => $person->id,
                    'organization_id' => $person->organization_id,
                    'role_type' => $this->form['role_type'] ?? 'STAFF',
                    'role_title' => $this->form['role_title'] ?? 'Organization Admin',
                    'start_date' => now(),
                    'status' => 'active',
                    'created_by' => $user->id,
                ]);
                Log::info('Person affiliation created', ['person_id' => $person->id]);

                // Assign Organization Admin role
                $user->assignRole('Organization Admin');
                Log::info('Role assigned', ['user_id' => $user->id]);

                // Send email verification notification
                $user->sendEmailVerificationNotification();
                Log::info('Verification notification sent', ['user_id' => $user->id]);

                DB::commit();
                Log::info('DB commit successful', ['user_id' => $user->id, 'person_id' => $person->id]);

                    // Try to send mail after commit
                    try {
                        Mail::to($user->email)->send(new \App\Mail\AdminWelcomeEmail($user, $Organization, $temporaryPassword));
                    } catch (\Exception $e) {
                        Log::error('Welcome email send error', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                        session()->flash('error', 'Verification email could not be sent. Please check your email address.');
                        return;
                    }

                session()->flash('success', 'Registration successful! Please check your email to verify your account.');
                return redirect()->route('login');
            } catch (\Exception $e) {
                if ($user) {
                    $user->delete();
                }
                DB::rollBack();
                session()->flash('error', 'Registration failed. Please try again.');
                Log::error('Registration DB error: ' . $e->getMessage(), ['exception' => $e]);
                return;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Registration failed. Please try again.');
            Log::error('Registration validation or logic error: ' . $e->getMessage(), ['exception' => $e]);
            return;
        }
    }

    private function createContactInformation(Person $person)
    {
        // Phone
        if (!empty($this->form['phone'])) {
            Phone::create([
                'person_id' => $person->id,
                'phone_id' => \App\Helpers\IdGenerator::generatePhoneId(),
                'number' => $this->form['phone'],
                'type' => 'mobile',
                'is_primary' => true,
                'status' => 'active',
                'created_by' => $person->user_id,
            ]);
        }

        // Email
        if (!empty($this->form['email'])) {
            EmailAddress::create([
                'person_id' => $person->id,
                'email_id' => \App\Helpers\IdGenerator::generateEmailId(),
                'email' => $this->form['email'],
                'type' => 'personal',
                'is_primary' => true,
                'status' => 'active',
                'created_by' => $person->user_id,
            ]);
        }

        // ...existing code...
    }
    public function render()
    {
        return view('livewire.person.person-self-registration')
        ->layout('layouts.auth-card');
    }
}
