@extends('layouts.admin')

@php($editing = $slide->exists)

@section('title', $editing ? 'Editar slide' : 'Novo slide')
@section('page-title', $editing ? 'Editar slide' : 'Novo slide')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <p class="text-sm font-medium text-blue-600">{{ $apresentacao->evento->titulo }}</p>
        <h2 class="text-2xl font-bold text-gray-900">{{ $editing ? 'Atualizar slide' : 'Criar slide' }}</h2>
        <p class="mt-1 text-sm text-gray-600">Apresentação: {{ $apresentacao->titulo }}</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.treinamentos.slides.update', $slide) : route('admin.treinamentos.slides.store', $apresentacao) }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="grid gap-6 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="titulo" class="mb-2 block text-sm font-semibold text-gray-700">Título do slide</label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $slide->titulo) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label for="ordem" class="mb-2 block text-sm font-semibold text-gray-700">Ordem</label>
                    <input type="number" min="1" id="ordem" name="ordem" value="{{ old('ordem', $slide->ordem ?: 1) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div>
                <label for="conteudo" class="mb-2 block text-sm font-semibold text-gray-700">
                    <i class="fa-solid fa-wand-magic-sparkles text-blue-500 mr-1"></i> Conteúdo do Slide
                </label>
                <p class="mb-3 text-xs text-gray-500">Use o editor abaixo para formatar o conteúdo com textos, imagens, cores e efeitos visuais.</p>
                <textarea id="conteudo" name="conteudo">{!! old('conteudo', $slide->conteudo) !!}</textarea>
            </div>

            {{-- Preview do slide --}}
            <div x-data="{ showPreview: false }" class="border-t border-gray-100 pt-4">
                <button type="button" @click="showPreview = !showPreview" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                    <i class="fa-solid fa-eye"></i>
                    <span x-text="showPreview ? 'Ocultar pré-visualização' : 'Pré-visualizar slide'"></span>
                </button>
                <div x-show="showPreview" x-transition class="mt-4 rounded-xl overflow-hidden border border-gray-200 shadow-lg">
                    <div class="aspect-video bg-gradient-to-br from-indigo-900 via-purple-900 to-blue-900 p-8 md:p-12 flex flex-col items-center justify-center text-center relative">
                        <h2 id="preview-titulo" class="text-2xl md:text-4xl font-bold text-white mb-6"></h2>
                        <div id="preview-conteudo" class="text-base md:text-lg text-white/80 leading-relaxed max-w-3xl slide-html-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.treinamentos.apresentacoes.show', $apresentacao) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fa-solid fa-check mr-2"></i>
                {{ $editing ? 'Salvar slide' : 'Criar slide' }}
            </button>
        </div>
    </form>
</div>

<style>
    .slide-html-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem auto; }
    .slide-html-content h1, .slide-html-content h2, .slide-html-content h3 { color: #fff; margin-bottom: 0.5rem; }
    .slide-html-content p { margin-bottom: 0.5rem; }
    .slide-html-content ul, .slide-html-content ol { text-align: left; margin: 0.5rem auto; max-width: 600px; }
    .slide-html-content li { margin-bottom: 0.25rem; }
    .slide-html-content a { color: #93c5fd; text-decoration: underline; }
    .slide-html-content blockquote { border-left: 4px solid rgba(255,255,255,0.3); padding-left: 1rem; font-style: italic; opacity: 0.9; }
    .slide-html-content table { border-collapse: collapse; margin: 0.5rem auto; }
    .slide-html-content th, .slide-html-content td { border: 1px solid rgba(255,255,255,0.2); padding: 0.5rem 1rem; }

    /* TinyMCE overrides */
    .tox-tinymce { border-radius: 0.75rem !important; border-color: #d1d5db !important; }
    .tox .tox-toolbar__primary { background: #f9fafb !important; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ config('app.tinymce_api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#conteudo',
        language: 'pt_BR',
        language_url: 'https://cdn.tiny.cloud/1/{{ config('app.tinymce_api_key') }}/tinymce/6/langs/pt_BR.js',
        height: 450,
        menubar: 'edit view insert format table',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'emoticons', 'preview'
        ],
        toolbar: [
            'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
            'forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'image media link | table emoticons charmap | blockquote hr | removeformat code fullscreen preview'
        ].join(' | '),
        block_formats: 'Parágrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Título 4=h4; Citação=blockquote; Código=pre',
        font_size_formats: '10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 42px 48px 56px 64px 72px',
        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 16px;
                line-height: 1.6;
                padding: 1rem;
                color: #1f2937;
            }
            img { max-width: 100%; height: auto; border-radius: 0.5rem; }
            h1 { font-size: 2.5rem; font-weight: 700; }
            h2 { font-size: 2rem; font-weight: 700; }
            h3 { font-size: 1.5rem; font-weight: 600; }
            blockquote { border-left: 4px solid #3b82f6; padding-left: 1rem; color: #6b7280; font-style: italic; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #d1d5db; padding: 0.5rem; }
            th { background: #f3f4f6; }
        `,
        images_upload_url: '{{ route("admin.treinamentos.upload-imagem") }}',
        images_upload_credentials: true,
        automatic_uploads: true,
        images_reuse_filename: false,
        file_picker_types: 'image',
        images_upload_handler: function(blobInfo, progress) {
            return new Promise(function(resolve, reject) {
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('_token', '{{ csrf_token() }}');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("admin.treinamentos.upload-imagem") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = function(e) {
                    progress(e.loaded / e.total * 100);
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var json = JSON.parse(xhr.responseText);
                        resolve(json.location);
                    } else {
                        reject('Erro ao enviar imagem: ' + xhr.status);
                    }
                };

                xhr.onerror = function() {
                    reject('Erro de conexão ao enviar imagem.');
                };

                xhr.send(formData);
            });
        },
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save();
                updatePreview();
            });
        },
        promotion: false,
        branding: false,
    });

    // Preview
    var tituloInput = document.getElementById('titulo');
    if (tituloInput) {
        tituloInput.addEventListener('input', updatePreview);
    }

    function updatePreview() {
        var previewTitulo = document.getElementById('preview-titulo');
        var previewConteudo = document.getElementById('preview-conteudo');
        if (previewTitulo) {
            previewTitulo.textContent = document.getElementById('titulo').value || 'Título do slide';
        }
        if (previewConteudo && tinymce.get('conteudo')) {
            previewConteudo.innerHTML = tinymce.get('conteudo').getContent();
        }
    }

    // Initial preview
    setTimeout(updatePreview, 500);
});
</script>
@endpush