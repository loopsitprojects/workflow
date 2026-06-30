<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Loops' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('loops-icon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Quill.js for Rich Text Editing -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--color-bg-secondary); color: var(--color-text-primary); transition: background-color 0.3s, color 0.3s; }
        .dark body, body.dark { background-color: var(--color-bg-secondary); }

        /* Premium dark background — subtle blue-tinted radial gradient */
        .dark main, html.dark body { background-color: #0b1120; }

        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(17, 24, 39, 0.75); border-color: rgba(255, 255, 255, 0.06); }
        .nav-active { color: var(--color-text-primary); font-weight: 600; border-bottom: 2px solid var(--color-text-primary); }

        /* Light mode: soft shadow. Dark mode: visible depth without harsh outline */
        .card-shadow { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 4px 16px rgba(0, 0, 0, 0.04); }
        .dark .card-shadow { box-shadow: 0 1px 0 rgba(255,255,255,0.04) inset, 0 4px 24px rgba(0, 0, 0, 0.35); }
    </style>
    <script>
        // Check for saved theme or default to system preference
        const initialTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (initialTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                current: initialTheme,
                toggle() {
                    this.current = this.current === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.current);
                    document.documentElement.classList.toggle('dark', this.current === 'dark');
                }
            });

            Alpine.data('quillEditor', (initialValue) => ({
                content: initialValue,
                init() {
                    const quill = new Quill(this.$refs.editor, {
                        theme: 'snow',
                        placeholder: 'Start typing...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['clean']
                            ]
                        }
                    });

                    // Set initial content if any
                    if (this.content) {
                        quill.clipboard.dangerouslyPasteHTML(this.content);
                    }

                    // Update hidden textarea on change
                    quill.on('text-change', () => {
                        this.content = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                    });
                }
            }));
        })
    </script>
</head>
<body class="antialiased bg-bg-secondary text-text-primary transition-colors duration-300"
      :class="{ 'dark': $store.theme.current === 'dark' }">
    <x-navigation />

    <main class="max-w-[1440px] mx-auto px-8 py-8">
        {{ $slot }}
    </main>
    <x-notification-panel />
    <x-toast />
</body>
</html>
