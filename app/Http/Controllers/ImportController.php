<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\ReadmeFile;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function store(Request $request)
    {
        $files = $request->file('importFiles', []);

        if (empty($files)) {
            return back()->withErrors(['importFiles' => app()->getLocale() === 'fr'
                ? 'Veuillez sélectionner au moins un fichier.'
                : 'Please select at least one file.'])->withInput();
        }

        $allowed  = ['md', 'txt', 'markdown'];
        $maxBytes = 10 * 1024 * 1024;

        foreach ($files as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowed)) {
                return back()->withErrors(['importFiles' => app()->getLocale() === 'fr'
                    ? "« {$file->getClientOriginalName()} » doit être .md, .txt ou .markdown."
                    : "« {$file->getClientOriginalName()} » must be .md, .txt or .markdown."])->withInput();
            }
            if ($file->getSize() > $maxBytes) {
                return back()->withErrors(['importFiles' => app()->getLocale() === 'fr'
                    ? "« {$file->getClientOriginalName()} » dépasse 10 Mo."
                    : "« {$file->getClientOriginalName()} » exceeds 10 MB."])->withInput();
            }
        }

        $targetFolderId = $request->input('importToFolderId') ?: null;

        if ($request->filled('importNewFolderName')) {
            $folder = Folder::create([
                'user_id' => auth()->id(),
                'name'    => $request->input('importNewFolderName'),
            ]);
            $targetFolderId = $folder->id;
        }

        $mergeSortOrder = $request->input('mergeSortOrder', 'name');
        $mergeOnImport  = $request->boolean('mergeOnImport');

        $uploadedFilesData = [];
        foreach ($files as $file) {
            $uploadedFilesData[] = [
                'name'          => $file->getClientOriginalName(),
                'content'       => $file->getContent(),
                'last_modified' => $file->getMTime(),
            ];
        }

        usort($uploadedFilesData, fn($a, $b) => $mergeSortOrder === 'date'
            ? $a['last_modified'] <=> $b['last_modified']
            : strcasecmp($a['name'], $b['name'])
        );

        if ($mergeOnImport) {
            $merged = '';
            foreach ($uploadedFilesData as $data) {
                $merged .= "# {$data['name']}\n\n{$data['content']}\n\n---\n\n";
            }
            ReadmeFile::create([
                'user_id'   => auth()->id(),
                'name'      => 'Merged_' . now()->format('Ymd_His') . '.md',
                'content'   => $merged,
                'folder_id' => $targetFolderId,
            ]);
        } else {
            foreach ($uploadedFilesData as $data) {
                ReadmeFile::create([
                    'user_id'   => auth()->id(),
                    'name'      => $data['name'],
                    'content'   => $data['content'],
                    'folder_id' => $targetFolderId,
                ]);
            }
        }

        $count = count($files);
        return redirect()->route('dashboard')->with('success', app()->getLocale() === 'fr'
            ? "{$count} fichier(s) importé(s) avec succès."
            : "{$count} file(s) imported successfully.");
    }
}
