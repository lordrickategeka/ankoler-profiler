<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PersonImportService;

class TestTemplateGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:template-generation {category?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test template generation for import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $category = $this->argument('category') ?? 'sacco';
        
        $this->info("Testing template generation for: {$category}");
        
        $service = new PersonImportService();
        
        // Get headers
        $headers = $service->getRoleSpecificTemplateHeaders($category);
        
        $this->info("Template headers for {$category}:");
        foreach ($headers as $key => $description) {
            $this->line("  {$key} => {$description}");
        }
        
        // Generate CSV template file
        $this->info("Generating CSV template file...");
        $csvTemplatePath = $service->generateTemplateFile($category, "Test Organization");
        $this->info("CSV Template generated at: {$csvTemplatePath}");
        
        // Generate Excel template file
        $this->info("Generating Excel template file...");
        try {
            $excelTemplatePath = $service->generateExcelTemplateFile($category, "Test Organization");
            $this->info("Excel Template generated at: {$excelTemplatePath}");
        } catch (\Exception $e) {
            $this->error("Failed to generate Excel template: " . $e->getMessage());
        }
        
        // Read and display first few lines of the CSV template
        if (file_exists($csvTemplatePath)) {
            $this->info("CSV Template content preview:");
            $lines = file($csvTemplatePath, FILE_IGNORE_NEW_LINES);
            foreach (array_slice($lines, 0, 5) as $index => $line) {
                $this->line("  Line " . ($index + 1) . ": {$line}");
            }
        }
        
        return 0;
    }
}
