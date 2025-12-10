#!/usr/bin/env php
<?php

echo "🚀 PROFILER DATABASE POPULATION SCRIPT 🚀\n\n";

echo "This script will populate your database with sample data for testing the filtering module.\n\n";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    echo "❌ Error: Please run this script from the project root directory.\n";
    exit(1);
}

echo "📋 What this script will create:\n";
echo "   • Fresh database with migrations\n";
echo "   • Sample users, organizations, and persons\n";
echo "   • Organization-specific filter configurations\n";
echo "   • Phone numbers and email addresses\n";
echo "   • Person affiliations\n\n";

echo "⚠️  WARNING: This will DROP all existing data!\n";
echo "Do you want to continue? (y/N): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "❌ Operation cancelled.\n";
    exit(0);
}

echo "\n🔄 Starting database population...\n\n";

// Run fresh migrations and basic seeders
echo "1️⃣ Running fresh migrations and basic seeders...\n";
system('php artisan migrate:fresh --seed');

echo "\n2️⃣ Creating additional organizations...\n";
system('php artisan tinker --execute="App\Models\Organization::factory()->count(5)->create(); echo \'✅ Created 5 additional organizations\' . PHP_EOL;"');

echo "\n3️⃣ Creating additional users...\n";
system('php artisan tinker --execute="App\Models\User::factory()->count(8)->create(); echo \'✅ Created 8 additional users\' . PHP_EOL;"');

echo "\n4️⃣ Creating additional persons...\n";
system('php artisan tinker --execute="App\Models\Person::factory()->count(30)->create(); echo \'✅ Created 30 additional persons\' . PHP_EOL;"');

echo "\n5️⃣ Creating filter configurations...\n";
system('php artisan db:seed --class=FilterConfigurationSeeder');

echo "\n6️⃣ Creating person affiliations...\n";
system('php artisan tinker --execute="App\Models\PersonAffiliation::factory()->count(15)->create(); echo \'✅ Created 15 person affiliations\' . PHP_EOL;"');

echo "\n7️⃣ Creating phone numbers...\n";
system('php artisan tinker --execute="App\Models\Phone::factory()->count(25)->create(); echo \'✅ Created 25 phone numbers\' . PHP_EOL;"');

echo "\n8️⃣ Creating email addresses...\n";
system('php artisan tinker --execute="App\Models\EmailAddress::factory()->count(20)->create(); echo \'✅ Created 20 email addresses\' . PHP_EOL;"');

echo "\n🎉 DATABASE POPULATION COMPLETE! 🎉\n\n";

// Show final summary
system('php artisan tinker --execute="
echo \'📊 FINAL DATABASE SUMMARY:\' . PHP_EOL;
echo \'👥 Users: \' . App\Models\User::count() . PHP_EOL;
echo \'🏢 Organizations: \' . App\Models\Organization::count() . PHP_EOL;
echo \'👤 Persons: \' . App\Models\Person::count() . PHP_EOL;
echo \'🔗 Person Affiliations: \' . App\Models\PersonAffiliation::count() . PHP_EOL;
echo \'📞 Phone Numbers: \' . App\Models\Phone::count() . PHP_EOL;
echo \'📧 Email Addresses: \' . App\Models\EmailAddress::count() . PHP_EOL;
echo \'🔍 Filter Configurations: \' . App\Models\FilterConfiguration::count() . PHP_EOL;
echo PHP_EOL . \'🔑 LOGIN CREDENTIALS:\' . PHP_EOL;
echo \'Email: admin@gmail.com\' . PHP_EOL;
echo \'Password: qwertyui\' . PHP_EOL;
echo PHP_EOL . \'🌐 Access the application at: http://127.0.0.1:8000/persons\' . PHP_EOL;
"');

echo "\n✅ You can now test the sophisticated filtering module!\n";
echo "💡 Run 'php artisan serve' to start the development server.\n\n";
