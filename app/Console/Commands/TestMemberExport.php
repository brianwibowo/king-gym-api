<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\MembersExport;
use Maatwebsite\Excel\Facades\Excel;

class TestMemberExport extends Command
{
    protected $signature = 'test:member-export';
    protected $description = 'Test member export generation';

    public function handle()
    {
        $this->info('Starting member export test...');

        try {
            $export = new MembersExport();

            $this->info('Headers count: ' . count($export->headings()));
            $this->info('Collection count: ' . $export->collection()->count());

            // Test generating the file
            $filename = storage_path('app/test-member-export.xlsx');
            Excel::store($export, 'test-member-export.xlsx');

            if (file_exists($filename)) {
                $size = filesize($filename);
                $this->info("✅ File generated successfully!");
                $this->info("File size: " . number_format($size / 1024, 2) . " KB");
                $this->info("File path: {$filename}");
            } else {
                $this->error("❌ File was not created!");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
