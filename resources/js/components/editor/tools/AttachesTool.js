/**
 * Custom AttachesTool for Editor.js
 * Provides modern upload card UI with real-time percentage progress bar,
 * live uploaded size / total size metrics, server processing states, and full styling.
 */

const EXT_COLORS = {
	pdf: { bg: '#FEE2E2', text: '#DC2626', border: '#FECACA', label: 'PDF' },
	doc: { bg: '#DBEAFE', text: '#1D4ED8', border: '#BFDBFE', label: 'DOC' },
	docx: { bg: '#DBEAFE', text: '#1D4ED8', border: '#BFDBFE', label: 'DOCX' },
	xls: { bg: '#D1FAE5', text: '#047857', border: '#A7F3D0', label: 'XLS' },
	xlsx: { bg: '#D1FAE5', text: '#047857', border: '#A7F3D0', label: 'XLSX' },
	csv: { bg: '#D1FAE5', text: '#047857', border: '#A7F3D0', label: 'CSV' },
	ppt: { bg: '#FFEDD5', text: '#C2410C', border: '#FED7AA', label: 'PPT' },
	pptx: { bg: '#FFEDD5', text: '#C2410C', border: '#FED7AA', label: 'PPTX' },
	zip: { bg: '#EDE9FE', text: '#6D28D9', border: '#DDD6FE', label: 'ZIP' },
	rar: { bg: '#EDE9FE', text: '#6D28D9', border: '#DDD6FE', label: 'RAR' },
	txt: { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0', label: 'TXT' },
	default: { bg: '#EEF2FF', text: '#4338CA', border: '#E0E7FF', label: 'FILE' },
};

const formatBytes = (bytes) => {
	if (!bytes || bytes === 0) return '0 B';
	const k = 1024;
	const sizes = ['B', 'KB', 'MB', 'GB'];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
};

export default class AttachesTool {
	static get toolbox() {
		return {
			icon: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>`,
			title: 'Lampiran / File',
		};
	}

	static get isReadOnlySupported() {
		return true;
	}

	constructor({ data, config, api, readOnly }) {
		this.api = api;
		this.readOnly = readOnly;
		this.config = config || {};
		this.data = {
			file: data?.file || {},
			title: data?.title || '',
		};

		this.wrapper = null;
		this.titleInput = null;
		this.progressBar = null;
		this.percentText = null;
		this.statusText = null;
		this.metricsText = null;
	}

	render() {
		this.wrapper = document.createElement('div');
		this.wrapper.classList.add('my-4', 'w-full', 'font-sans');

		if (this.data.file && this.data.file.url) {
			this.renderUploadedView();
		} else {
			this.renderInitialView();
		}

		return this.wrapper;
	}

	renderInitialView() {
		this.wrapper.innerHTML = '';

		const uploadBtn = document.createElement('div');
		uploadBtn.className =
			'group relative border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-2xl p-6 bg-slate-50/70 hover:bg-blue-50/30 dark:bg-slate-900/40 dark:hover:bg-blue-950/20 transition-all duration-200 cursor-pointer flex flex-col items-center justify-center text-center';

		uploadBtn.innerHTML = `
			<div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-xs">
				<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
			</div>
			<div class="text-[14px] font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
				${this.config.buttonText || 'Unggah File Lampiran'}
			</div>
			<p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">
				Mendukung PDF, DOCX, XLSX, PPTX, ZIP, RAR hingga 100 MB
			</p>
		`;

		if (!this.readOnly) {
			uploadBtn.addEventListener('click', () => this.selectAndUploadFile());
		}

		this.wrapper.appendChild(uploadBtn);
	}

	renderProgressView(file) {
		this.wrapper.innerHTML = '';

		const ext = file.name.split('.').pop()?.toLowerCase() || '';
		const style = EXT_COLORS[ext] || EXT_COLORS.default;

		const progressCard = document.createElement('div');
		progressCard.className =
			'border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 bg-white dark:bg-slate-900 shadow-sm flex flex-col gap-3.5 transition-all';

		progressCard.innerHTML = `
			<div class="flex items-center justify-between gap-3">
				<div class="flex items-center gap-3 min-w-0">
					<div class="w-10 h-10 rounded-xl flex items-center justify-center text-[11px] font-black tracking-wider uppercase shrink-0 border"
						style="background-color: ${style.bg}; color: ${style.text}; border-color: ${style.border};">
						${style.label}
					</div>
					<div class="min-w-0">
						<h5 class="text-[13px] font-bold text-slate-800 dark:text-slate-100 truncate">${file.name}</h5>
						<p class="text-[11px] text-slate-400 font-medium">${formatBytes(file.size)}</p>
					</div>
				</div>
				<div class="flex items-center gap-3 shrink-0">
					<span class="upload-percent text-[13px] font-black text-blue-600 dark:text-blue-400">0%</span>
					<button type="button" class="cancel-upload-btn text-xs font-bold text-slate-400 hover:text-red-500 transition-colors px-2 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30">
						Batal
					</button>
				</div>
			</div>

			<!-- Progress Bar Container -->
			<div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden relative">
				<div class="upload-bar bg-linear-to-r from-blue-600 via-indigo-600 to-blue-500 h-full rounded-full transition-all duration-150 ease-out" style="width: 0%"></div>
			</div>

			<!-- Status note -->
			<div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
				<span class="upload-status flex items-center gap-1.5">
					<span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-ping"></span>
					<span class="status-msg">Mengunggah file...</span>
				</span>
				<span class="upload-metrics text-[10px] text-slate-400 font-medium">0 / ${formatBytes(file.size)}</span>
			</div>
		`;

		this.progressBar = progressCard.querySelector('.upload-bar');
		this.percentText = progressCard.querySelector('.upload-percent');
		this.statusText = progressCard.querySelector('.status-msg');
		this.metricsText = progressCard.querySelector('.upload-metrics');

		progressCard.querySelector('.cancel-upload-btn')?.addEventListener('click', () => {
			if (this.currentAbortController) {
				this.currentAbortController.abort();
			}
			this.renderInitialView();
		});

		this.wrapper.appendChild(progressCard);
	}

	renderUploadedView() {
		this.wrapper.innerHTML = '';

		const file = this.data.file || {};
		const ext = file.extension || file.name?.split('.').pop()?.toLowerCase() || '';
		const style = EXT_COLORS[ext] || EXT_COLORS.default;

		const card = document.createElement('div');
		card.className =
			'group border border-slate-200 dark:border-slate-800 hover:border-blue-400/80 dark:hover:border-blue-600/80 rounded-2xl p-4 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-4';

		const leftPart = document.createElement('div');
		leftPart.className = 'flex items-center gap-3.5 min-w-0 flex-1';

		// Extension Badge
		const badge = document.createElement('div');
		badge.className =
			'w-11 h-11 rounded-xl flex items-center justify-center text-[11px] font-black tracking-wider uppercase shrink-0 border';
		badge.style.backgroundColor = style.bg;
		badge.style.color = style.text;
		badge.style.borderColor = style.border;
		badge.textContent = style.label;

		// Info & Editable Title
		const info = document.createElement('div');
		info.className = 'flex-1 min-w-0';

		const titleWrapper = document.createElement('div');
		titleWrapper.className = 'flex items-center gap-1.5';

		this.titleInput = document.createElement('input');
		this.titleInput.type = 'text';
		this.titleInput.className =
			'w-full bg-transparent text-[13px] font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 rounded px-1 -ml-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-colors';
		this.titleInput.value = this.data.title || file.name || 'File Lampiran';
		this.titleInput.placeholder = 'Ketik judul file lampiran...';
		this.titleInput.readOnly = this.readOnly;
		this.titleInput.addEventListener('input', (e) => {
			this.data.title = e.target.value;
		});

		titleWrapper.appendChild(this.titleInput);

		const meta = document.createElement('div');
		meta.className = 'flex items-center gap-2 mt-0.5 text-[11px] text-slate-400 dark:text-slate-500';
		meta.innerHTML = `
			<span class="font-bold uppercase">${ext}</span>
			${file.size ? `<span>•</span><span>${formatBytes(file.size)}</span>` : ''}
			${file.url ? `<span>•</span><span class="text-emerald-600 dark:text-emerald-400 font-semibold">Tersimpan</span>` : ''}
		`;

		info.appendChild(titleWrapper);
		info.appendChild(meta);

		leftPart.appendChild(badge);
		leftPart.appendChild(info);

		// Actions
		const rightPart = document.createElement('div');
		rightPart.className = 'flex items-center gap-2 shrink-0';

		if (file.url) {
			const downloadBtn = document.createElement('a');
			downloadBtn.href = file.url;
			downloadBtn.target = '_blank';
			downloadBtn.rel = 'noopener noreferrer';
			downloadBtn.className =
				'w-9 h-9 rounded-xl bg-slate-100 hover:bg-blue-600 text-slate-600 hover:text-white dark:bg-slate-800 dark:hover:bg-blue-600 dark:text-slate-300 dark:hover:text-white flex items-center justify-center transition-all shadow-2xs';
			downloadBtn.title = 'Buka / Unduh Berkas';
			downloadBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`;
			rightPart.appendChild(downloadBtn);
		}

		if (!this.readOnly) {
			const replaceBtn = document.createElement('button');
			replaceBtn.type = 'button';
			replaceBtn.className =
				'w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition-all';
			replaceBtn.title = 'Ganti Berkas';
			replaceBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>`;
			replaceBtn.addEventListener('click', () => this.selectAndUploadFile());
			rightPart.appendChild(replaceBtn);
		}

		card.appendChild(leftPart);
		card.appendChild(rightPart);
		this.wrapper.appendChild(card);
	}

	renderErrorView(errMsg) {
		this.wrapper.innerHTML = '';

		const errorCard = document.createElement('div');
		errorCard.className =
			'border border-red-200 dark:border-red-900/50 rounded-2xl p-4.5 bg-red-50/60 dark:bg-red-950/30 flex items-center justify-between gap-4 transition-all';

		errorCard.innerHTML = `
			<div class="flex items-center gap-3 min-w-0">
				<div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
					<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
				</div>
				<div class="min-w-0">
					<h5 class="text-[13px] font-bold text-red-900 dark:text-red-200">Gagal Mengunggah Berkas</h5>
					<p class="text-[11px] text-red-600 dark:text-red-400 truncate mt-0.5">${errMsg || 'Terjadi kesalahan saat mengunggah berkas.'}</p>
				</div>
			</div>
			<div class="flex items-center gap-2 shrink-0">
				<button type="button" class="cancel-err-btn px-2.5 py-1.5 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-[11px] font-bold transition-colors">
					Batal
				</button>
				<button type="button" class="retry-btn px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-[11px] font-bold transition-colors shadow-xs">
					Coba Lagi
				</button>
			</div>
		`;

		errorCard.querySelector('.cancel-err-btn')?.addEventListener('click', () => this.renderInitialView());
		errorCard.querySelector('.retry-btn')?.addEventListener('click', () => this.selectAndUploadFile());
		this.wrapper.appendChild(errorCard);
	}

	selectAndUploadFile() {
		const input = document.createElement('input');
		input.type = 'file';
		input.accept =
			this.config.types ||
			'.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv';

		input.addEventListener('change', async () => {
			const file = input.files?.[0];
			if (!file) return;

			this.renderProgressView(file);

			try {
				const uploader = this.config.uploader?.uploadByFile;
				if (!uploader || typeof uploader !== 'function') {
					throw new Error('Uploader function is not provided');
				}

				this.currentAbortController = new AbortController();
				const startTime = Date.now();

				const response = await uploader(
					file,
					(progress) => {
						if (!this.progressBar || !this.percentText) return;

						const pct = Math.min(progress.percent || 0, 100);
						this.progressBar.style.width = `${pct}%`;
						this.percentText.textContent = `${pct}%`;

						const elapsedSec = (Date.now() - startTime) / 1000;
						let speedStr = '';
						if (elapsedSec > 0.5 && progress.loaded > 0) {
							const speed = progress.loaded / elapsedSec;
							speedStr = ` • ${formatBytes(speed)}/s`;
						}

						if (this.metricsText) {
							this.metricsText.textContent = `${formatBytes(progress.loaded)} / ${formatBytes(progress.total)}${speedStr}`;
						}

						if (pct >= 100 && this.statusText) {
							this.statusText.textContent = 'Memproses & memindai keamanan di server...';
						}
					},
					this.currentAbortController.signal,
				);

				if (response?.canceled) {
					this.renderInitialView();
					return;
				}

				if (response?.success === 1 && response?.file) {
					this.data = {
						file: response.file,
						title: this.data.title || response.file.name || file.name,
					};
					this.renderUploadedView();

					// Dispatch change in Editor.js
					const currentIdx = this.api.blocks.getCurrentBlockIndex();
					if (currentIdx >= 0) {
						this.api.blocks.getBlockByIndex(currentIdx)?.dispatchChange();
					}
				} else {
					const errorMsg =
						response?.error?.message ||
						response?.message ||
						this.config.errorMessage ||
						'Gagal mengunggah file.';
					this.renderErrorView(errorMsg);
				}
			} catch (err) {
				console.error('[AttachesTool] Upload error:', err);
				this.renderErrorView(err.message || 'Gagal mengunggah file.');
			}
		});

		input.click();
	}

	save() {
		return {
			file: this.data.file || {},
			title: this.titleInput ? this.titleInput.value : this.data.title || '',
		};
	}

	validate(savedData) {
		return Boolean(savedData.file && savedData.file.url);
	}
}
