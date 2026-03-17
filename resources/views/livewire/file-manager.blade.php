<?php

use Livewire\Volt\Component;
use App\Models\Folder;
use App\Models\ReadmeFile;
use Livewire\WithFileUploads;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component {
    use WithFileUploads;

    public $folders;
    public $files;
    public $selectedFolderId = null;
    public $selectedFileId = null;
    public $selectedFileIds = [];
    
    // Form fields
    public $newFolderName = '';
    public $newFileName = '';
    public $newFileContent = '';
    public $isEditing = false;
    public $search = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $uid = auth()->id();
        $this->folders = Folder::where('user_id', $uid)->whereNull('parent_id')->get();
        $query = ReadmeFile::where('user_id', $uid);
        if ($this->selectedFolderId) {
            $query->where('folder_id', $this->selectedFolderId);
        }
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        $this->files = $query->latest()->get();
    }

    public function selectFolder($id)
    {
        $this->selectedFolderId = $id;
        $this->selectedFileId = null;
        $this->isEditing = false;
        $this->selectedFileIds = [];
        $this->loadData();
    }

    public function selectFile($id)
    {
        $this->selectedFileId = $id;
        $file = ReadmeFile::find($id);
        $this->newFileName = $file->name;
        $this->newFileContent = $file->content;
        $this->isEditing = true;
    }

    public function createFolder()
    {
        $this->validate(['newFolderName' => 'required|string|max:255']);
        Folder::create(['user_id' => auth()->id(), 'name' => $this->newFolderName]);
        $this->newFolderName = '';
        $this->loadData();
    }

    public function createFile()
    {
        $this->validate([
            'newFileName' => 'required|string|max:255',
            'newFileContent' => 'required'
        ]);
        
        ReadmeFile::updateOrCreate(
            ['id' => $this->selectedFileId, 'user_id' => auth()->id()],
            [
                'user_id' => auth()->id(),
                'name' => $this->newFileName,
                'content' => $this->newFileContent,
                'folder_id' => $this->selectedFolderId,
            ]
        );

        $this->reset(['newFileName', 'newFileContent', 'isEditing', 'selectedFileId']);
        $this->loadData();
    }

    public function deleteFile($id)
    {
        ReadmeFile::where('id', $id)->where('user_id', auth()->id())->delete();
        if ($this->selectedFileId == $id) {
            $this->reset(['selectedFileId', 'isEditing', 'newFileName', 'newFileContent']);
        }
        $this->loadData();
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedFileIds) === count($this->files)) {
            $this->selectedFileIds = [];
        } else {
            $this->selectedFileIds = $this->files->pluck('id')->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedFileIds)) return;

        ReadmeFile::whereIn('id', $this->selectedFileIds)->where('user_id', auth()->id())->delete();
        $this->selectedFileIds = [];
        $this->loadData();
    }

    public function exportPdf($id)
    {
        $file = ReadmeFile::findOrFail($id);
        
        // CSS for Professional PDF
        $css = '
            body { font-family: sans-serif; line-height: 1.6; color: #333; margin: 20px; }
            h1, h2, h3 { border-bottom: 1px solid #eee; padding-bottom: 5px; }
            pre { 
                background: #f6f8fa; padding: 16px; border-radius: 6px; 
                white-space: pre-wrap; word-wrap: break-word; font-size: 12px; border: 1px solid #ddd;
            }
            code { 
                font-family: monospace; background: #f0f0f0; padding: 2px 4px; border-radius: 3px; font-size: 90%; word-break: break-all;
            }
            pre code { background: transparent; padding: 0; }
            img { max-width: 100%; height: auto; display: block; margin: 10px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; table-layout: fixed; }
            table, th, td { border: 1px solid #dfe2e5; }
            th, td { padding: 8px 12px; text-align: left; word-wrap: break-word; font-size: 13px; }
            th { background-color: #f6f8fa; }
            blockquote { margin: 0; padding: 0 1em; color: #6a737d; border-left: 0.25em solid #dfe2e5; }
        ';

        $html = '
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>'.$css.'</style>
            </head>
            <body>
                <div style="margin-bottom: 30px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px;">
                    <h1 style="margin: 0;">' . htmlspecialchars($file->name) . '</h1>
                    <p style="color: #666; font-size: 12px;">Généré par README Hub le ' . now()->format('d/m/Y H:i') . '</p>
                </div>
                ' . $file->rendered_content . '
            </body>
            </html>
        ';

        return response()->streamDownload(function () use ($html) {
            echo Pdf::loadHTML($html)->output();
        }, $file->name . '.pdf');
    }

    public function mergeSelected()
    {
        if (empty($this->selectedFileIds)) return;

        $filesToMerge = ReadmeFile::whereIn('id', $this->selectedFileIds)->orderBy('created_at')->get();
        
        $mergedContent = "";
        foreach($filesToMerge as $file) {
            $mergedContent .= "# " . $file->name . "\n\n" . $file->content . "\n\n---\n\n";
        }

        ReadmeFile::create([
            'name' => 'Merged_' . now()->format('Ymd_His') . '.md',
            'content' => $mergedContent,
            'folder_id' => $this->selectedFolderId
        ]);

        $this->selectedFileIds = [];
        $this->loadData();
    }
}; ?>

<div class="flex flex-col md:flex-row gap-6 h-full min-h-[600px]">
    <!-- Sidebar -->
    <div class="w-full md:w-1/4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
        <h3 class="font-bold text-lg mb-4 dark:text-white border-b pb-2 dark:border-gray-700">Folders</h3>
        <div class="space-y-1">
            <button wire:click="selectFolder(null)" class="w-full text-left px-3 py-2 rounded-lg transition {{ $selectedFolderId === null ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300' }}">
                📁 All Files
            </button>
            @foreach($folders as $folder)
                <button wire:click="selectFolder({{ $folder->id }})" class="w-full text-left px-3 py-2 rounded-lg transition {{ $selectedFolderId == $folder->id ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300' }}">
                    📂 {{ $folder->name }}
                </button>
            @endforeach
        </div>

        <div class="mt-6 pt-6 border-t dark:border-gray-700">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">New Folder</label>
            <input type="text" wire:model="newFolderName" placeholder="Folder name..." class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white mb-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <button wire:click="createFolder" class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white py-2 rounded-lg transition text-sm font-bold">Add Folder</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg flex-1 overflow-y-auto border border-gray-200 dark:border-gray-700">
            @if(!$isEditing)
                <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                    <h3 class="font-bold text-2xl dark:text-white">Files Explorer</h3>
                    <div class="flex gap-2">
                        @if(!empty($selectedFileIds))
                            <button wire:click="mergeSelected" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition shadow-md font-bold flex items-center disabled:opacity-50">
                                <span wire:loading wire:target="mergeSelected" class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                                Merge ({{ count($selectedFileIds) }})
                            </button>
                            <button 
                                onclick="confirm('Êtes-vous sûr de vouloir supprimer les ' + {{ count($selectedFileIds) }} + ' fichiers sélectionnés ? Cette action est irréversible.') || event.stopImmediatePropagation()"
                                wire:click="deleteSelected" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition shadow-md font-bold flex items-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete Selected
                            </button>
                        @endif
                        <a href="{{ route('import') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition shadow-md font-bold inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import
                        </a>
                        <button wire:click="$set('isEditing', true)" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition shadow-md font-bold inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            New Readme
                        </button>
                    </div>
                </div>
                
                <div class="relative mb-6">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Search files by name..." class="w-full rounded-xl border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 h-11">
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 rounded-xl overflow-hidden border dark:border-gray-700 shadow-inner">
                    <div class="flex items-center p-4 border-b dark:border-gray-700 bg-white dark:bg-gray-800">
                        <input type="checkbox" wire:click="toggleSelectAll" {{ count($selectedFileIds) === count($files) && count($files) > 0 ? 'checked' : '' }} class="mr-4 rounded-md border-gray-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5 cursor-pointer">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Select All Files</span>
                    </div>

                    <div class="divide-y dark:divide-gray-700">
                        @forelse($files as $file)
                            <div class="flex items-center p-4 hover:bg-white dark:hover:bg-gray-850 transition group border-l-4 border-transparent hover:border-indigo-500">
                                <input type="checkbox" wire:model.live="selectedFileIds" value="{{ (string)$file->id }}" class="mr-4 rounded-md border-gray-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5 cursor-pointer">
                                <button wire:click="selectFile({{ $file->id }})" class="text-left flex-1 group-hover:translate-x-1 transition-transform">
                                    <span class="font-bold text-gray-900 dark:text-white block text-lg">{{ $file->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">📅 {{ $file->created_at->format('M d, Y - H:i') }}</span>
                                </button>
                                <div class="flex gap-4 items-center">
                                    <button 
                                        wire:click="exportPdf({{ $file->id }})" 
                                        wire:loading.attr="disabled"
                                        wire:target="exportPdf({{ $file->id }})"
                                        class="bg-white dark:bg-gray-700 border border-indigo-200 dark:border-indigo-900 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900 transition shadow-sm flex items-center disabled:opacity-50"
                                    >
                                        <span wire:loading wire:target="exportPdf({{ $file->id }})" class="mr-1">
                                            <svg class="animate-spin h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </span>
                                        <span wire:loading.remove wire:target="exportPdf({{ $file->id }})">📄</span>
                                        Export PDF
                                    </button>
                                    <button onclick="confirm('Supprimer ce fichier ?') || event.stopImmediatePropagation()" wire:click="deleteFile({{ $file->id }})" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20">
                                <div class="text-5xl mb-4">📂</div>
                                <div class="text-gray-500 dark:text-gray-400 font-bold text-xl">No README files found.</div>
                                <p class="text-gray-400 mt-2">Start by creating a new file or importing existing ones.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="flex flex-col h-full min-h-[600px]">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4 border-b pb-4 dark:border-gray-700">
                        <div class="flex-1 min-w-[300px]">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">File Name</label>
                            <input type="text" wire:model="newFileName" placeholder="README.md" class="w-full rounded-xl border-gray-300 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-lg">
                        </div>
                        <div class="flex gap-2 pt-5">
                            <button wire:click="$set('isEditing', false)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 font-bold px-4 py-2 transition hover:underline">Cancel</button>
                            <button wire:click="createFile" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2 rounded-xl transition font-bold shadow-lg transform active:scale-95 disabled:opacity-50 inline-flex items-center">
                                <span wire:loading wire:target="createFile" class="mr-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-6 min-h-[400px]">
                        <div class="flex flex-col">
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Editor (Markdown)</label>
                                <span class="text-xs text-gray-400 font-bold">Supports Github Flavored Markdown</span>
                            </div>
                            <textarea wire:model.live="newFileContent" class="flex-1 w-full rounded-xl border-gray-300 dark:bg-gray-700 dark:text-white font-mono text-sm p-5 focus:border-indigo-500 focus:ring-indigo-500 shadow-inner" placeholder="# Hello World..."></textarea>
                        </div>
                        
                        <div class="flex flex-col">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Live Preview</label>
                            <div class="flex-1 p-8 border dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-950 overflow-auto prose dark:prose-invert max-w-none shadow-inner">
                                @if($newFileContent)
                                    {!! (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($newFileContent) !!}
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400 italic">Preview will appear here as you type...</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
