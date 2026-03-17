<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $projects = \App\Models\Folder::create(['name' => 'My Projects']);
        $docs = \App\Models\Folder::create(['name' => 'Documentation']);

        \App\Models\ReadmeFile::create([
            'name' => 'README.md',
            'content' => "# Welcome to README Hub\n\nThis is a sample readme file.\nYou can **edit** it, *preview* it, and even **export** it to PDF!",
            'folder_id' => $projects->id,
        ]);

        \App\Models\ReadmeFile::create([
            'name' => 'Features.md',
            'content' => "# Features List\n\n- Read Markdown\n- Organize in Folders\n- Merge Files\n- Export to PDF",
            'folder_id' => $projects->id,
        ]);
    }
}
