import Quill from 'quill';
import Delta from 'quill-delta';
import 'quill/dist/quill.snow.css';

const SizeStyle = Quill.import('attributors/style/size');
SizeStyle.whitelist = ['8pt', '10pt', '12pt', '14pt', '16pt', '18pt', '20pt', '24pt', '28pt', '36pt'];
Quill.register(SizeStyle, true);

const FontStyle = Quill.import('attributors/style/font');
FontStyle.whitelist = ['arial', 'times-new-roman', 'courier-new', 'georgia', 'verdana'];
Quill.register(FontStyle, true);

// O Quill não preserva por padrão width/height aplicados diretamente em TD.
// Registrando-os como formatos de bloco, os valores passam a fazer parte do
// conteúdo do editor e sobrevivem ao ciclo criar -> salvar -> editar.
const Parchment = Quill.import('parchment');
const TableCellWidth = new Parchment.StyleAttributor('tableCellWidth', 'width', { scope: Parchment.Scope.BLOCK });
const TableCellHeight = new Parchment.StyleAttributor('tableCellHeight', 'height', { scope: Parchment.Scope.BLOCK });
const TableCellAlign = new Parchment.StyleAttributor('tableCellAlign', 'text-align', { scope: Parchment.Scope.BLOCK });
Quill.register(TableCellWidth, true);
Quill.register(TableCellHeight, true);
Quill.register(TableCellAlign, true);

function inferImageAlign(node) {
    const explicit = node.getAttribute('data-align');
    if (explicit === 'left' || explicit === 'center' || explicit === 'right') {
        return explicit;
    }

    const marginLeft = String(node.style.marginLeft || '').trim();
    const marginRight = String(node.style.marginRight || '').trim();
    if (marginLeft === 'auto' && marginRight === 'auto') return 'center';
    if (marginLeft === 'auto') return 'right';

    const parent = node.parentElement;
    if (parent) {
        const textAlign = String(parent.style.textAlign || '').trim();
        if (parent.classList.contains('ql-align-center') || textAlign === 'center') return 'center';
        if (parent.classList.contains('ql-align-right') || textAlign === 'right') return 'right';
    }

    return 'left';
}

function applyParentBlockAlign(node, alignment) {
    const parent = node.parentElement;
    if (!parent || parent.tagName === 'TD' || parent.tagName === 'TH') return;

    parent.style.textAlign = alignment;
    parent.classList.remove('ql-align-center', 'ql-align-right', 'ql-align-left', 'ql-align-justify');
    if (alignment === 'center' || alignment === 'right') {
        parent.classList.add(`ql-align-${alignment}`);
    }
}

function applyImageAlign(node, alignment) {
    const value = alignment === 'center' || alignment === 'right' ? alignment : 'left';
    node.setAttribute('data-align', value);
    node.style.float = 'none';
    node.style.height = 'auto';
    node.style.display = 'inline-block';
    node.style.marginLeft = '0';
    node.style.marginRight = '0';
    node.style.verticalAlign = 'middle';

    const cell = node.closest?.('td, th');
    if (cell) {
        cell.style.textAlign = value;
        return;
    }

    applyParentBlockAlign(node, value);
}

function parseImageWidth(width) {
    const parsed = Math.round(Number(String(width ?? '').replace(/px$/i, '')));
    return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
}

function readImageWidth(node) {
    return parseImageWidth(
        node.getAttribute('data-width') || node.getAttribute('width') || node.style.width
    );
}

function applyImageWidth(node, width) {
    const adjusted = Math.max(80, Math.min(1200, parseImageWidth(width) || readImageWidth(node) || 80));
    node.setAttribute('width', String(adjusted));
    node.setAttribute('data-width', String(adjusted));
    node.style.width = `${adjusted}px`;
    node.style.height = 'auto';
    node.style.maxWidth = '100%';
    return adjusted;
}

function imageValueFromNode(node) {
    const width = readImageWidth(node);
    return {
        src: DocumentoImage.sanitize(node.getAttribute('src') || ''),
        width: width ? String(width) : '',
        align: inferImageAlign(node),
    };
}

function applyImagePresentation(node, value = {}) {
    const src = typeof value === 'string' ? value : (value?.src || node.getAttribute('src') || '');
    if (src) node.setAttribute('src', DocumentoImage.sanitize(src));
    const width = typeof value === 'object' ? value.width : node.getAttribute('width');
    if (width) applyImageWidth(node, width);
    applyImageAlign(node, typeof value === 'object' ? (value.align || inferImageAlign(node)) : inferImageAlign(node));
}

// Mantém imagens antigas do InfoVISA, que podem estar salvas como /storage/..., e
// as imagens recém-enviadas com URL absoluta. O formato padrão do Quill aceita
// apenas http(s) e data:, convertendo caminhos locais em uma imagem inválida.
const BaseImage = Quill.import('formats/image');
class DocumentoImage extends BaseImage {
    static sanitize(url) {
        const value = String(url || '').trim();
        if (/^(?:https?:|data:image\/|\/storage\/|storage\/)/i.test(value)) {
            return value;
        }

        return BaseImage.sanitize(value);
    }

    static create(value) {
        const src = typeof value === 'string' ? value : String(value?.src || '');
        const node = super.create(src);
        node.setAttribute('src', this.sanitize(src));
        applyImagePresentation(node, value);
        return node;
    }

    static value(domNode) {
        return imageValueFromNode(domNode);
    }

    static formats(domNode) {
        const formats = {};
        ['alt', 'height'].forEach((attribute) => {
            if (domNode.hasAttribute(attribute)) {
                formats[attribute] = domNode.getAttribute(attribute);
            }
        });
        const width = readImageWidth(domNode);
        if (width) formats.width = String(width);
        const align = inferImageAlign(domNode);
        if (align) formats.align = align;
        return formats;
    }

    format(name, value) {
        if (name === 'width') {
            if (value) applyImageWidth(this.domNode, value);
            else {
                this.domNode.removeAttribute('width');
                this.domNode.removeAttribute('data-width');
                this.domNode.style.width = '';
            }
            return;
        }

        if (name === 'align') {
            applyImageAlign(this.domNode, value);
            return;
        }

        if (name === 'table' || name === 'tableCellWidth' || name === 'tableCellHeight' || name === 'tableCellAlign') {
            return;
        }

        super.format(name, value);
    }
}
Quill.register(DocumentoImage, true);

class DocumentoRichEditor {
    constructor(target, config = {}) {
        this.target = typeof target === 'string' ? document.querySelector(target) : target;
        this.config = config;
        this.listeners = {};

        if (!this.target) {
            throw new Error('Área do editor não encontrada.');
        }

        this.mount();
    }

    mount() {
        const wrapper = document.createElement('section');
        wrapper.className = 'documento-rich-editor';
        wrapper.innerHTML = `
            <div class="documento-rich-editor__chrome">
            <div class="documento-rich-editor__options">
                <label class="documento-rich-editor__a4-option">
                    <input type="checkbox" class="js-documento-a4" checked>
                    <span>Visualizar em A4</span>
                </label>
                <span class="documento-rich-editor__hint">A4 não altera o conteúdo salvo. Clique numa imagem para ajustar; arraste as divisórias da tabela. Enter na última linha ou Tab na última célula cria outra linha.</span>
            </div>
            <div class="ql-toolbar ql-snow documento-rich-editor__toolbar">
                <span class="ql-formats">
                    <select class="ql-header"><option selected></option><option value="1"></option><option value="2"></option><option value="3"></option><option value="4"></option><option value="5"></option><option value="6"></option></select>
                    <select class="ql-font"><option selected value="arial"></option><option value="times-new-roman"></option><option value="georgia"></option><option value="verdana"></option><option value="courier-new"></option></select>
                    <select class="ql-size"><option value="8pt">8 pt</option><option selected value="10pt">10 pt</option><option value="12pt">12 pt</option><option value="14pt">14 pt</option><option value="16pt">16 pt</option><option value="18pt">18 pt</option><option value="20pt">20 pt</option><option value="24pt">24 pt</option><option value="28pt">28 pt</option><option value="36pt">36 pt</option></select>
                </span>
                <span class="ql-formats">
                    <button class="ql-bold" aria-label="Negrito"></button><button class="ql-italic" aria-label="Itálico"></button><button class="ql-underline" aria-label="Sublinhado"></button><button class="ql-strike" aria-label="Tachado"></button>
                    <select class="ql-color"></select><select class="ql-background"></select>
                </span>
                <span class="ql-formats">
                    <button class="ql-align" value="" aria-label="Alinhar à esquerda"></button><button class="ql-align" value="center" aria-label="Centralizar"></button><button class="ql-align" value="right" aria-label="Alinhar à direita"></button><button class="ql-align" value="justify" aria-label="Justificar"></button>
                    <button class="ql-list" value="ordered" aria-label="Lista numerada"></button><button class="ql-list" value="bullet" aria-label="Lista com marcadores"></button><button class="ql-indent" value="-1" aria-label="Diminuir recuo"></button><button class="ql-indent" value="+1" aria-label="Aumentar recuo"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-link" aria-label="Inserir link"></button><button class="ql-image" aria-label="Inserir imagens"></button><button class="ql-table" aria-label="Inserir tabela">▦</button><button class="ql-table-row-above" aria-label="Inserir linha acima">↥</button><button class="ql-table-row-below" aria-label="Inserir linha abaixo">↧</button><button class="ql-clean" aria-label="Limpar formatação"></button>
                </span>
                <span class="ql-formats">
                    <button type="button" class="ql-undo" aria-label="Desfazer">↶</button><button type="button" class="ql-redo" aria-label="Refazer">↷</button>
                </span>
            </div>
            </div>
            <div class="documento-rich-editor__canvas documento-rich-editor__canvas--a4"><div class="documento-rich-editor__content"></div></div>`;

        this.target.style.display = 'none';
        this.target.insertAdjacentElement('afterend', wrapper);
        this.wrapper = wrapper;
        this.canvas = wrapper.querySelector('.documento-rich-editor__canvas');
        this.contentElement = wrapper.querySelector('.documento-rich-editor__content');

        this.quill = new Quill(this.contentElement, {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: wrapper.querySelector('.ql-toolbar'),
                    handlers: {
                        image: () => this.selectImage(),
                        table: () => this.insertTable(),
                        'table-row-above': () => this.insertTableRow('above'),
                        'table-row-below': () => this.insertTableRow('below'),
                        undo: () => this.quill.history.undo(),
                        redo: () => this.quill.history.redo(),
                    },
                },
                history: { delay: 500, maxStack: 100, userOnly: true },
                table: true,
            },
        });

        wrapper.querySelector('.js-documento-a4').addEventListener('change', (event) => {
            this.canvas.classList.toggle('documento-rich-editor__canvas--a4', event.target.checked);
        });

        this.createImageControls();
        this.canvas.addEventListener('scroll', () => {
            if (this.activeImage) this.positionImageControls(this.activeImage);
        }, { passive: true });
        this.installImageClipboard();
        this.installTableResizers();
        this.installTableKeyboardNavigation();
        this.quill.on('text-change', () => this.emit('input change keyup'));
        this.emit('init');
    }

    on(events, callback) {
        events.split(/\s+/).forEach((event) => {
            this.listeners[event] = this.listeners[event] || [];
            this.listeners[event].push(callback);
        });
    }

    emit(events) {
        events.split(/\s+/).forEach((event) => (this.listeners[event] || []).forEach((callback) => callback()));
    }

    getContent(options = {}) {
        if (options?.format === 'text') {
            return this.quill.getText().replace(/\n$/, '');
        }

        this.restoreImagePresentation();
        const holder = document.createElement('div');
        holder.innerHTML = this.quill.root.innerHTML;
        this.quill.root.querySelectorAll('table').forEach((table, index) => {
            const percents = this.measureTableColumnPercents(table);
            const clone = holder.querySelectorAll('table')[index];
            if (clone && percents.length) this.writeTableColumnPercents(clone, percents, { colgroup: true });
        });
        holder.querySelectorAll('img').forEach((img) => {
            img.classList.remove('documento-rich-editor__image-selected');
            applyImagePresentation(img, imageValueFromNode(img));
        });
        return holder.innerHTML;
    }

    setContent(html) {
        const safe = html || '<p><br></p>';
        if (/<table[\s>]/i.test(safe)) {
            this.quill.root.innerHTML = safe;
        } else {
            this.quill.clipboard.dangerouslyPasteHTML(safe, 'silent');
        }
        this.restoreImagePresentation();
        this.restoreTableColumnWidths();
        requestAnimationFrame(() => this.restoreTableColumnWidths());
    }

    restoreImagePresentation() {
        this.quill.root.querySelectorAll('img').forEach((img) => applyImagePresentation(img, imageValueFromNode(img)));
    }

    insertContent(html) {
        const selection = this.quill.getSelection(true) || { index: this.quill.getLength(), length: 0 };
        this.quill.clipboard.dangerouslyPasteHTML(selection.index, html, 'user');
        this.quill.setSelection(selection.index + 1, 0, 'silent');
    }

    async selectImage() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/gif,image/webp';
        input.multiple = true;
        input.addEventListener('change', async () => {
            const files = Array.from(input.files || []).filter((file) => /^image\/(jpeg|png|gif|webp)$/i.test(file.type) || /\.(jpe?g|png|gif|webp)$/i.test(file.name));
            if (!files.length) return;

            const errors = [];
            const selection = this.quill.getSelection(true) || { index: this.quill.getLength(), length: 0 };
            let index = selection.index;

            for (const [offset, file] of files.entries()) {
                try {
                    const location = await this.uploadImage(file);
                    this.quill.insertEmbed(index, 'image', { src: location, align: 'left' }, 'user');
                    index += 1;
                    if (files.length > 1 && offset < files.length - 1) {
                        this.quill.insertText(index, '\n', 'user');
                        index += 1;
                    }
                } catch (error) {
                    errors.push(`${file.name}: ${error?.message || 'falha no envio'}`);
                }
            }

            this.quill.setSelection(index, 0, 'silent');
            if (errors.length) {
                const prefixo = errors.length === files.length
                    ? 'Não foi possível enviar as imagens.'
                    : 'Algumas imagens não foram enviadas.';
                alert(`${prefixo}\n${errors.join('\n')}`);
            }
        });
        input.click();
    }

    async uploadImage(file) {
        if (this.config.images_upload_handler) {
            return this.config.images_upload_handler({ blob: () => file, filename: () => file.name });
        }

        const formData = new FormData();
        formData.append('file', file);
        const response = await fetch(this.config.images_upload_url, { method: 'POST', body: formData, credentials: 'same-origin' });
        const json = await response.json();
        if (!response.ok || !json.location) throw new Error(json.message || 'Falha no envio da imagem.');
        return json.location;
    }

    createImageControls() {
        const controls = document.createElement('div');
        controls.className = 'documento-rich-editor__image-controls';
        controls.hidden = true;
        controls.innerHTML = `
            <span class="documento-rich-editor__image-label">Tamanho da imagem</span>
            <button type="button" class="js-image-smaller" title="Diminuir imagem">−</button>
            <input type="range" class="js-image-width" min="80" max="1200" step="1" aria-label="Largura da imagem">
            <button type="button" class="js-image-larger" title="Aumentar imagem">+</button>
            <output class="js-image-width-value"></output>
            <span class="documento-rich-editor__image-separator"></span>
            <button type="button" class="js-image-align-left" title="Alinhar imagem à esquerda">⇤</button>
            <button type="button" class="js-image-align-center" title="Centralizar imagem">↔</button>
            <button type="button" class="js-image-align-right" title="Alinhar imagem à direita">⇥</button>`;
        this.wrapper.appendChild(controls);
        this.imageControls = controls;
        this.imageWidthInput = controls.querySelector('.js-image-width');
        this.imageWidthValue = controls.querySelector('.js-image-width-value');

        this.imageResizeDrag = null;
        this.imageControlsLeft = null;

        this.contentElement.addEventListener('click', (event) => {
            const image = event.target.closest('img');
            if (!image) {
                this.hideImageControls();
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.showImageControls(image);
        });

        document.addEventListener('click', (event) => {
            if (!this.activeImage || this.imageControls.contains(event.target) || this.activeImage.contains(event.target)) {
                return;
            }

            this.hideImageControls();
        });

        controls.addEventListener('pointerdown', (event) => {
            event.stopPropagation();
            if (event.target.closest('input[type="range"]')) return;
            event.preventDefault();
        });

        this.imageWidthInput.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (!this.activeImage) return;
            this.imageResizeDrag = {
                pointerId: event.pointerId,
                startX: event.clientX,
                startWidth: this.activeImageWidth(),
            };
            this.imageWidthInput.setPointerCapture(event.pointerId);
        });
        this.imageWidthInput.addEventListener('pointermove', (event) => {
            if (!this.imageResizeDrag || event.pointerId !== this.imageResizeDrag.pointerId) return;
            this.resizeActiveImage(this.imageResizeDrag.startWidth + (event.clientX - this.imageResizeDrag.startX), { live: true });
        });
        const finishImageDrag = (event) => {
            if (!this.imageResizeDrag || event.pointerId !== this.imageResizeDrag.pointerId) return;
            this.imageResizeDrag = null;
            this.resizeActiveImage(this.activeImageWidth());
        };
        this.imageWidthInput.addEventListener('pointerup', finishImageDrag);
        this.imageWidthInput.addEventListener('pointercancel', finishImageDrag);

        const stepImage = (delta) => (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.resizeActiveImage(this.activeImageWidth() + delta);
        };
        controls.querySelector('.js-image-smaller').addEventListener('pointerdown', stepImage(-20));
        controls.querySelector('.js-image-larger').addEventListener('pointerdown', stepImage(20));
        controls.querySelector('.js-image-align-left').addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.alignActiveImage('left');
        });
        controls.querySelector('.js-image-align-center').addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.alignActiveImage('center');
        });
        controls.querySelector('.js-image-align-right').addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.alignActiveImage('right');
        });
    }

    installImageClipboard() {
        this.quill.clipboard.addMatcher('IMG', (node) => {
            const value = imageValueFromNode(node);
            if (!value.src) return new Delta();
            const attributes = {};
            if (value.width) attributes.width = String(value.width);
            if (value.align) attributes.align = value.align;
            return new Delta().insert({ image: value }, attributes);
        });
    }

    activeImageWidth() {
        return readImageWidth(this.activeImage) || Math.round(this.activeImage?.getBoundingClientRect().width || 80);
    }

    showImageControls(image) {
        this.activeImage = image;
        this.imageControlsLeft = null;
        const width = this.activeImageWidth();
        this.imageWidthInput.value = String(Math.max(80, Math.min(1200, width)));
        this.imageWidthValue.value = `${width}px`;
        this.imageWidthValue.textContent = `${width}px`;
        this.imageControls.hidden = false;
        this.contentElement.querySelectorAll('img.documento-rich-editor__image-selected')
            .forEach((item) => item.classList.remove('documento-rich-editor__image-selected'));
        image.classList.add('documento-rich-editor__image-selected');

        const currentAlign = inferImageAlign(image);
        this.imageControls.querySelectorAll('[class*="js-image-align-"]').forEach((button) => {
            const isActive = button.classList.contains(`js-image-align-${currentAlign}`);
            button.classList.toggle('is-active', isActive);
        });

        this.positionImageControls(image);
        this.imageControlsLeft = parseFloat(this.imageControls.style.left) || 8;
    }

    positionImageControls(image) {
        const imageRect = image.getBoundingClientRect();
        const wrapperRect = this.wrapper.getBoundingClientRect();
        const controlWidth = this.imageControls.offsetWidth || 512;
        const top = Math.max(0, imageRect.top - wrapperRect.top + 8);
        const left = this.imageControlsLeft != null
            ? this.imageControlsLeft
            : Math.max(8, Math.min(imageRect.left - wrapperRect.left + 8, wrapperRect.width - controlWidth - 8));
        this.imageControls.style.top = `${top}px`;
        this.imageControls.style.left = `${left}px`;
    }

    hideImageControls() {
        if (this.imageControls) this.imageControls.hidden = true;
        this.contentElement.querySelectorAll('img.documento-rich-editor__image-selected')
            .forEach((item) => item.classList.remove('documento-rich-editor__image-selected'));
        this.activeImage = null;
        this.imageControlsLeft = null;
        this.imageResizeDrag = null;
    }

    resizeActiveImage(width, { live = false } = {}) {
        if (!this.activeImage) return;

        const adjustedWidth = applyImageWidth(this.activeImage, width);
        if (!live) {
            const blot = Quill.find(this.activeImage);
            if (blot?.format) {
                blot.format('width', String(adjustedWidth));
            }
            this.emit('input change keyup');
        }

        this.imageWidthInput.value = String(adjustedWidth);
        this.imageWidthValue.value = `${adjustedWidth}px`;
        this.imageWidthValue.textContent = `${adjustedWidth}px`;
    }

    alignActiveImage(alignment) {
        if (!this.activeImage) return;

        applyImageAlign(this.activeImage, alignment);
        const blot = Quill.find(this.activeImage);
        if (blot?.format) {
            blot.format('align', alignment);
        }
        const index = blot ? this.quill.getIndex(blot) : null;
        if (index != null) {
            this.quill.formatLine(index, 1, 'align', alignment === 'left' ? false : alignment);
        }
        const cell = this.activeImage.closest('td, th');
        const cellBlot = cell ? Quill.find(cell) : null;
        if (cellBlot?.format) {
            cellBlot.format('tableCellAlign', alignment);
        }

        this.emit('input change keyup');
        this.showImageControls(this.activeImage);
    }

    installTableResizers() {
        const edgeTolerance = 8;
        let resizingRow = null;
        let resizingColumn = null;

        const allCells = () => Array.from(this.contentElement.querySelectorAll('td, th'));

        const nearestCell = (event, distanceFromEdge) => {
            const directCell = event.target.closest?.('td, th');
            const candidates = directCell && this.contentElement.contains(directCell)
                ? [directCell, ...allCells().filter((cell) => cell !== directCell)]
                : allCells();

            return candidates
                .map((cell) => ({ cell, rect: cell.getBoundingClientRect() }))
                .filter(({ rect }) => event.clientX >= rect.left - edgeTolerance
                    && event.clientX <= rect.right + edgeTolerance
                    && event.clientY >= rect.top - edgeTolerance
                    && event.clientY <= rect.bottom + edgeTolerance)
                .map((item) => ({ ...item, distance: distanceFromEdge(item.rect) }))
                .filter(({ distance }) => distance <= edgeTolerance)
                .sort((a, b) => a.distance - b.distance)[0]?.cell || null;
        };

        const findResizableRow = (event) => {
            const cell = nearestCell(event, (rect) => Math.abs(event.clientY - rect.bottom));
            if (!cell) return null;
            return cell.parentElement?.tagName === 'TR' ? cell.parentElement : null;
        };

        const findResizableColumn = (event) => {
            const cell = nearestCell(event, (rect) => Math.abs(event.clientX - rect.right));
            if (!cell) return null;
            const row = cell.parentElement;
            const table = cell.closest('table');
            if (!table || !row) return null;
            return { cell, row, table, index: Array.from(row.cells).indexOf(cell) };
        };

        const clearColumnHighlight = () => {
            this.contentElement.querySelectorAll('.documento-rich-editor__column-resize-hover')
                .forEach((cell) => cell.classList.remove('documento-rich-editor__column-resize-hover'));
        };

        const highlightColumnBoundary = (column) => {
            clearColumnHighlight();
            if (!column) return;
            Array.from(column.table.rows).forEach((row) => {
                row.cells[column.index]?.classList.add('documento-rich-editor__column-resize-hover');
            });
        };

        const setResizeCursor = (mode = null) => {
            this.contentElement.classList.toggle('documento-rich-editor__content--column-resize', mode === 'column');
            this.contentElement.classList.toggle('documento-rich-editor__content--row-resize', mode === 'row');
        };

        this.contentElement.addEventListener('mousemove', (event) => {
            if (resizingRow || resizingColumn) return;
            const column = findResizableColumn(event);
            highlightColumnBoundary(column);
            setResizeCursor(column ? 'column' : (findResizableRow(event) ? 'row' : null));
        });

        this.contentElement.addEventListener('mouseleave', () => {
            if (!resizingRow && !resizingColumn) {
                clearColumnHighlight();
                setResizeCursor();
            }
        });

        this.contentElement.addEventListener('mousedown', (event) => {
            const column = findResizableColumn(event);
            if (column) {
                event.preventDefault();
                resizingColumn = {
                    ...column,
                    startX: event.clientX,
                    startWidth: column.cell.getBoundingClientRect().width,
                };
                column.table.style.tableLayout = 'fixed';
                document.body.classList.add('documento-rich-editor--resizing-column');
                return;
            }

            const row = findResizableRow(event);
            if (!row) return;

            event.preventDefault();
            const rect = row.getBoundingClientRect();
            resizingRow = { row, startY: event.clientY, startHeight: rect.height };
            document.body.classList.add('documento-rich-editor--resizing-row');
        });

        document.addEventListener('mousemove', (event) => {
            if (resizingColumn) {
                const width = Math.max(45, Math.round(resizingColumn.startWidth + event.clientX - resizingColumn.startX));
                Array.from(resizingColumn.table.rows).forEach((row) => {
                    const cell = row.cells[resizingColumn.index];
                    if (!cell) return;
                    cell.style.width = `${width}px`;
                    const blot = Quill.find(cell);
                    if (blot?.format) blot.format('tableCellWidth', `${width}px`);
                });
                return;
            }

            if (!resizingRow) return;
            const height = Math.max(28, Math.round(resizingRow.startHeight + event.clientY - resizingRow.startY));
            Array.from(resizingRow.row.cells).forEach((cell) => {
                cell.style.height = `${height}px`;
                const blot = Quill.find(cell);
                if (blot?.format) blot.format('tableCellHeight', `${height}px`);
            });
        });

        document.addEventListener('mouseup', () => {
            if (!resizingRow && !resizingColumn) return;
            const table = resizingColumn?.table || resizingRow?.row?.closest('table');
            const percents = table ? this.measureTableColumnPercents(table) : [];
            this.quill.update(Quill.sources.USER);
            if (table && percents.length) {
                this.writeTableColumnPercents(table, percents, { formatBlots: true });
            }
            this.emit('input change keyup');
            resizingRow = null;
            resizingColumn = null;
            clearColumnHighlight();
            setResizeCursor();
            document.body.classList.remove('documento-rich-editor--resizing-row');
            document.body.classList.remove('documento-rich-editor--resizing-column');
        });
    }

    restoreTableColumnWidths() {
        this.quill.root.querySelectorAll('table').forEach((table) => {
            const percents = this.readStoredColumnPercents(table);
            if (percents.length) this.writeTableColumnPercents(table, percents, { formatBlots: true });
        });
    }

    parseColumnSize(value) {
        const text = String(value || '').trim();
        if (!text) return null;
        const percent = text.match(/^([\d.]+)\s*%$/);
        if (percent) return { type: 'percent', value: Number(percent[1]) };
        const pixels = text.match(/^([\d.]+)\s*px$/i);
        if (pixels) return { type: 'px', value: Number(pixels[1]) };
        const number = Number(text);
        return Number.isFinite(number) && number > 0 ? { type: 'px', value: number } : null;
    }

    normalizePercents(values) {
        const total = values.reduce((sum, value) => sum + value, 0);
        if (total < 1) return [];
        const rounded = values.map((value) => Math.max(1, Math.round((value / total) * 1000) / 10));
        const drift = rounded.reduce((sum, value) => sum + value, 0) - 100;
        rounded[rounded.length - 1] = Math.max(1, Math.round((rounded[rounded.length - 1] - drift) * 10) / 10);
        return rounded;
    }

    readStoredColumnPercents(table) {
        const row = table?.rows?.[0];
        const cols = table?.querySelectorAll('col') || [];
        const sources = cols.length
            ? Array.from(cols).map((col) => col.style.width || col.getAttribute('width'))
            : Array.from(row?.cells || []).map((cell) => (
                cell.getAttribute('data-col-width') || cell.style.width || cell.getAttribute('width')
            ));
        const parsed = sources.map((value) => this.parseColumnSize(value));
        if (!parsed.length || parsed.some((item) => !item || !(item.value > 0))) return [];
        if (parsed.every((item) => item.type === 'percent')) {
            return this.normalizePercents(parsed.map((item) => item.value));
        }
        return this.normalizePercents(parsed.map((item) => item.value));
    }

    measureTableColumnPercents(table) {
        const row = table?.rows?.[0];
        if (!row) return [];
        const widths = Array.from(row.cells).map((cell) => cell.getBoundingClientRect().width);
        if (widths.every((width) => width > 1)) return this.normalizePercents(widths);
        return this.readStoredColumnPercents(table);
    }

    writeTableColumnPercents(table, percents, { colgroup = false, formatBlots = false } = {}) {
        table.style.tableLayout = 'fixed';
        table.style.width = '100%';
        if (colgroup) {
            table.querySelector('colgroup')?.remove();
            const group = table.ownerDocument.createElement('colgroup');
            percents.forEach((percent) => {
                const col = table.ownerDocument.createElement('col');
                const value = `${percent}%`;
                col.style.width = value;
                col.setAttribute('width', value);
                group.appendChild(col);
            });
            table.insertBefore(group, table.firstChild);
        }

        Array.from(table.rows).forEach((row) => {
            Array.from(row.cells).forEach((cell, index) => {
                if (percents[index] == null) return;
                const value = `${percents[index]}%`;
                cell.style.width = value;
                cell.setAttribute('width', value);
                cell.setAttribute('data-col-width', value);
                if (formatBlots) {
                    const blot = Quill.find(cell);
                    if (blot?.format) blot.format('tableCellWidth', value);
                }
            });
        });
    }

    installTableKeyboardNavigation() {
        this.quill.root.addEventListener('keydown', (event) => {
            if (!['Enter', 'Tab'].includes(event.key) || event.shiftKey) return;

            const tableModule = this.quill.getModule('table');
            if (!tableModule?.getTable) return;
            const [, row, cell] = tableModule.getTable();
            if (!row || !cell) return;

            const isLastRow = row.next == null;
            const isLastCell = cell.next == null;
            const shouldCreateRow = (event.key === 'Enter' && isLastRow)
                || (event.key === 'Tab' && isLastRow && isLastCell);
            if (!shouldCreateRow) return;

            event.preventDefault();
            event.stopPropagation();
            const columnIndex = typeof cell.cellOffset === 'function' ? cell.cellOffset() : 0;
            tableModule.insertRowBelow();

            requestAnimationFrame(() => {
                const tableElement = cell.domNode?.closest('table');
                const newRow = tableElement?.rows[tableElement.rows.length - 1];
                const targetCell = newRow?.cells[Math.max(0, Math.min(columnIndex, newRow.cells.length - 1))];
                const targetBlot = targetCell ? Quill.find(targetCell) : null;
                if (targetBlot) {
                    this.quill.setSelection(this.quill.getIndex(targetBlot), 0, Quill.sources.SILENT);
                    this.quill.focus();
                }
            });
        }, true);
    }

    insertTable() {
        const linhas = Number.parseInt(window.prompt('Número de linhas:', '3'), 10);
        const colunas = Number.parseInt(window.prompt('Número de colunas:', '3'), 10);
        if (!linhas || !colunas || linhas < 1 || colunas < 1) return;

        const table = this.quill.getModule('table');
        if (table?.insertTable) {
            table.insertTable(linhas, colunas);
            return;
        }

        const cells = Array.from({ length: linhas }, () => `<tr>${Array.from({ length: colunas }, () => '<td>&nbsp;</td>').join('')}</tr>`).join('');
        this.insertContent(`<table><tbody>${cells}</tbody></table><p><br></p>`);
    }

    insertTableRow(position) {
        const table = this.quill.getModule('table');
        if (!table || typeof table.getTable !== 'function' || !table.getTable()[0]) {
            alert('Posicione o cursor dentro de uma célula da tabela antes de inserir uma linha.');
            return;
        }

        if (position === 'above') {
            table.insertRowAbove();
        } else {
            table.insertRowBelow();
        }
    }
}

// Mantém a pequena API usada pelas telas atuais, sem carregar TinyMCE ou chave externa.
const editors = new Map();
window.tinymce = {
    init(config) {
        const target = document.querySelector(config.selector);
        const editor = new DocumentoRichEditor(target, config);
        editors.set(target.id, editor);
        editor.ui = { registry: { addButton: () => {} } };
        if (config.setup) config.setup(editor);
        editor.emit('init');
        return Promise.resolve([editor]);
    },
    get(id) { return editors.get(id) || null; },
};

window.DocumentoRichEditor = DocumentoRichEditor;
