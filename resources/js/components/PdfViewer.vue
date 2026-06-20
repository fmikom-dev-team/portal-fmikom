<template>
  <div class="pdf-viewer" :class="{ 'pdf-viewer--fullscreen': isFullscreen }">
    <!-- Toolbar -->
    <div class="pdf-toolbar">

      <!-- Kiri: nama file -->
      <div class="pdf-toolbar__left">
        <span class="pdf-toolbar__filename">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
          <span class="pdf-toolbar__filename-text">{{ filename }}</span>
        </span>
      </div>

      <!-- Tengah: navigasi halaman -->
      <div class="pdf-toolbar__center">
        <button class="pdf-btn" :disabled="currentPage <= 1" @click="prevPage" aria-label="Halaman sebelumnya">&#8249;</button>
        <div class="pdf-page-input">
          <input
            type="number"
            :value="currentPage"
            :min="1"
            :max="totalPages"
            @change="onPageInput"
            @keyup.enter="onPageInput"
            aria-label="Halaman saat ini"
          />
          <span class="pdf-page-sep">/ {{ totalPages }}</span>
        </div>
        <button class="pdf-btn" :disabled="currentPage >= totalPages" @click="nextPage" aria-label="Halaman berikutnya">&#8250;</button>
      </div>

      <!-- Kanan: zoom + aksi -->
      <div class="pdf-toolbar__right">
        <button class="pdf-btn" :disabled="zoomLevel <= minZoom" @click="zoomOut" aria-label="Perkecil">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
        </button>

        <select class="pdf-zoom-select" :value="zoomLevel" @change="onZoomSelect" aria-label="Level zoom">
          <option v-for="z in zoomOptions" :key="z" :value="z">{{ z }}%</option>
        </select>

        <button class="pdf-btn" :disabled="zoomLevel >= maxZoom" @click="zoomIn" aria-label="Perbesar">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
        </button>

        <div class="pdf-toolbar__divider"></div>

        <button class="pdf-btn" @click="fitToWidth" title="Sesuaikan lebar" aria-label="Fit to width">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15,3 21,3 21,9"/><polyline points="9,21 3,21 3,15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
        </button>

        <button class="pdf-btn" @click="toggleFullscreen" :aria-label="isFullscreen ? 'Keluar fullscreen' : 'Fullscreen'">
          <svg v-if="!isFullscreen" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="8,3 3,3 3,8"/><polyline points="21,8 21,3 16,3"/><polyline points="3,16 3,21 8,21"/><polyline points="16,21 21,21 21,16"/></svg>
          <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="4,14 4,20 10,20"/><polyline points="20,10 20,4 14,4"/><line x1="14" y1="10" x2="21" y2="3"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
        </button>

        <a :href="src" download :aria-label="'Unduh ' + filename" class="pdf-btn">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </a>
      </div>
    </div>

    <!-- Viewer Body -->
    <div class="pdf-layout">
      <!-- Sidebar Thumbnail -->
      <div v-if="showThumbnails && totalPages > 1" class="pdf-thumbnails">
        <div
          v-for="page in totalPages"
          :key="page"
          class="pdf-thumbnail"
          :class="{ 'pdf-thumbnail--active': page === currentPage }"
          @click="goToPage(page)"
        >
          <canvas :ref="el => setThumbnailRef(el, page)" class="pdf-thumbnail__canvas"></canvas>
          <span>{{ page }}</span>
        </div>
      </div>

      <!-- Main Content -->
      <div class="pdf-body" ref="pdfBodyRef">
        <div v-if="loading" class="pdf-state">
          <div class="pdf-spinner"></div>
          <p>Memuat dokumen...</p>
        </div>

        <div v-else-if="error" class="pdf-state pdf-state--error">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <p>Gagal memuat dokumen</p>
          <button class="pdf-btn-retry" @click="loadPdf">Coba lagi</button>
        </div>

        <div v-show="!loading && !error" class="pdf-canvas-wrap" ref="canvasWrapRef">
          <canvas ref="canvasRef" class="pdf-canvas"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, shallowRef, watch, onMounted, onUnmounted, nextTick } from 'vue'
import * as pdfjsLib from 'pdfjs-dist'
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url'
pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc

const props = defineProps({
  src: { type: String, required: true },
  filename: { type: String, default: 'Dokumen.pdf' },
  showThumbnails: { type: Boolean, default: false },
  initialPage: { type: Number, default: 1 },
  initialZoom: { type: Number, default: 100 },
})

const emit = defineEmits(['page-change', 'load', 'error'])

const loading = ref(true)
const error = ref(false)
const currentPage = ref<number>(props.initialPage)
const totalPages = ref<number>(0)
const zoomLevel = ref<number>(props.initialZoom)
const isFullscreen = ref(false)
const isCompactViewport = ref(false)
// shallowRef: pdfjs uses private class fields (#pagesNumber, etc) that break
// when Vue wraps the object in a deep reactive Proxy via ref()
const pdfDoc = shallowRef(null)
const renderTask = shallowRef(null)

const canvasRef = ref(null)
const pdfBodyRef = ref(null)
const canvasWrapRef = ref(null)
const thumbnailRefs = ref({})

const minZoom = 25
const maxZoom = 300
const zoomStep = 25
const zoomOptions = [25, 33, 50, 75, 100, 125, 150, 175, 200, 300]

function setThumbnailRef(el: HTMLCanvasElement | null, page: number) {
  if (el) thumbnailRefs.value[page] = el
}

async function loadPdf() {
  if (!props.src) return
  loading.value = true
  error.value = false
  try {
    const loadingTask = pdfjsLib.getDocument({
      url: props.src,
      withCredentials: true,
    })
    pdfDoc.value = await loadingTask.promise
    totalPages.value = pdfDoc.value.numPages
    currentPage.value = Math.min(props.initialPage, totalPages.value)
    await renderPage(currentPage.value)
    if (isCompactViewport.value) {
      await fitToWidth()
    }
    emit('load', { totalPages: totalPages.value })
    if (props.showThumbnails) {
      await nextTick()
      renderAllThumbnails()
    }
  } catch (e) {
    error.value = true
    emit('error', e)
  } finally {
    loading.value = false
  }
}

async function renderPage(pageNum) {
  if (!pdfDoc.value || !canvasRef.value) return
  if (renderTask.value) renderTask.value.cancel()
  try {
    const page = await pdfDoc.value.getPage(pageNum)
    const viewport = page.getViewport({ scale: zoomLevel.value / 100 })
    const canvas = canvasRef.value
    const ctx = canvas.getContext('2d')
    canvas.height = viewport.height
    canvas.width = viewport.width
    renderTask.value = page.render({ canvasContext: ctx, viewport })
    await renderTask.value.promise
  } catch (e) {
    if (e?.name !== 'RenderingCancelledException') console.error('Render error:', e)
  }
}

async function renderAllThumbnails() {
  if (!pdfDoc.value) return
  for (let i = 1; i <= totalPages.value; i++) await renderThumbnail(i)
}

async function renderThumbnail(pageNum) {
  const canvas = thumbnailRefs.value[pageNum]
  if (!canvas || !pdfDoc.value) return
  try {
    const page = await pdfDoc.value.getPage(pageNum)
    const viewport = page.getViewport({ scale: 0.2 })
    const ctx = canvas.getContext('2d')
    canvas.height = viewport.height
    canvas.width = viewport.width
    await page.render({ canvasContext: ctx, viewport }).promise
  } catch {}
}

function prevPage() {
  if (currentPage.value > 1) goToPage(currentPage.value - 1)
}
function nextPage() {
  if (currentPage.value < totalPages.value) goToPage(currentPage.value + 1)
}
function goToPage(page: number) {
  const safePage = Number.isFinite(page) ? page : 1
  currentPage.value = Math.max(1, Math.min(safePage, totalPages.value || 1))
}
function onPageInput(e: Event) {
  const target = e.target as HTMLInputElement | null
  const val = Number.parseInt(target?.value ?? '', 10)
  if (!Number.isNaN(val)) goToPage(val)
}

function zoomIn() {
  zoomLevel.value = Math.min(maxZoom, zoomLevel.value + zoomStep)
}
function zoomOut() {
  zoomLevel.value = Math.max(minZoom, zoomLevel.value - zoomStep)
}
function onZoomSelect(e: Event) {
  const target = e.target as HTMLSelectElement | null
  const val = Number.parseInt(target?.value ?? '', 10)
  if (!Number.isNaN(val)) zoomLevel.value = val
}

async function fitToWidth() {
  if (!pdfDoc.value || !pdfBodyRef.value) return
  const page = await pdfDoc.value.getPage(currentPage.value)
  const viewport = page.getViewport({ scale: 1 })
  const containerWidth = pdfBodyRef.value.clientWidth - (isCompactViewport.value ? 24 : 48)
  const nextZoom = Math.round((containerWidth / viewport.width) * 100)
  zoomLevel.value = Math.max(minZoom, Math.min(maxZoom, nextZoom))
}

function toggleFullscreen() { isFullscreen.value = !isFullscreen.value }

function onKeydown(e: KeyboardEvent) {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') nextPage()
  if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') prevPage()
  if (e.key === '+' || e.key === '=') zoomIn()
  if (e.key === '-') zoomOut()
  if (e.key === 'Escape' && isFullscreen.value) isFullscreen.value = false
}

function updateViewportMode() {
  if (typeof window === 'undefined') return
  isCompactViewport.value = window.matchMedia('(max-width: 640px)').matches
}

watch(currentPage, async (val) => { await renderPage(val); emit('page-change', val) })
watch(zoomLevel, async () => { await renderPage(currentPage.value) })
watch(() => props.src, () => { loadPdf() })

onMounted(() => {
  updateViewportMode()
  window.addEventListener('keydown', onKeydown)
  window.addEventListener('resize', updateViewportMode)
  loadPdf()
})
onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('resize', updateViewportMode)
  if (renderTask.value) renderTask.value.cancel()
})
</script>

<style scoped>
/* ── Container ── */
.pdf-viewer {
  display: flex;
  flex-direction: column;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #d4d0c8;
  height: 100%;
  min-height: 700px;
  font-family: system-ui, -apple-system, sans-serif;
  font-size: 13px;
}
.pdf-viewer--fullscreen {
  position: fixed;
  inset: 0;
  z-index: 9999;
  border-radius: 0;
  height: 100dvh;
}

/* ── Toolbar ── */
.pdf-toolbar {
  display: flex;
  align-items: center;
  height: 44px;
  padding: 0 8px;
  background: #3c3c3c;
  color: #e0ddd6;
  gap: 4px;
  flex-shrink: 0;
}

.pdf-toolbar__left {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: center;
}

.pdf-toolbar__filename {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #c8c4bb;
  font-size: 12px;
  min-width: 0;
}

.pdf-toolbar__filename-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

.pdf-toolbar__center {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.pdf-toolbar__right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 2px;
  min-width: 0;
}

.pdf-toolbar__divider {
  width: 1px;
  height: 18px;
  background: #5a5a5a;
  margin: 0 4px;
}

/* ── Toolbar buttons ── */
.pdf-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  min-width: 30px;
  border-radius: 5px;
  border: none;
  background: transparent;
  color: #d0cdc6;
  cursor: pointer;
  font-size: 20px;
  line-height: 1;
  transition: background 0.15s, color 0.15s;
  text-decoration: none;
  padding: 0;
}
.pdf-btn:hover:not(:disabled) {
  background: #525252;
  color: #f0ece3;
}
.pdf-btn:disabled {
  opacity: 0.3;
  cursor: default;
}
.pdf-btn svg {
  display: block;
  flex-shrink: 0;
}

.pdf-btn-retry {
  background: none;
  border: 1px solid #6a8a7a;
  color: #7aaa8a;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  margin-top: 8px;
}

/* ── Page input ── */
.pdf-page-input {
  display: flex;
  align-items: center;
  gap: 5px;
}
.pdf-page-input input {
  width: 40px;
  height: 28px;
  background: #525252;
  border: 1px solid #6a6a6a;
  border-radius: 4px;
  color: #f0ece3;
  text-align: center;
  font-size: 12px;
  padding: 0;
  -moz-appearance: textfield;
}
.pdf-page-input input::-webkit-outer-spin-button,
.pdf-page-input input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}
.pdf-page-input input:focus {
  outline: none;
  border-color: #8ab4a0;
}
.pdf-page-sep {
  color: #9a9690;
  font-size: 12px;
  white-space: nowrap;
}

/* ── Zoom select ── */
.pdf-zoom-select {
  background: #525252;
  border: 1px solid #6a6a6a;
  border-radius: 4px;
  color: #d0cdc6;
  font-size: 12px;
  padding: 0 6px;
  height: 28px;
  cursor: pointer;
  appearance: auto;
}
.pdf-zoom-select:focus {
  outline: none;
  border-color: #8ab4a0;
}

/* ── Layout ── */
.pdf-layout {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* ── Body ── */
.pdf-body {
  flex: 1;
  overflow: auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 24px;
  background: #808080;
}

.pdf-canvas-wrap {
  display: inline-block;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  line-height: 0;
  max-width: 100%;
}
.pdf-canvas {
  display: block;
  max-width: 100%;
  height: auto;
}

/* ── States ── */
.pdf-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #ccc;
  font-size: 13px;
  padding: 60px 20px;
  width: 100%;
}
.pdf-state--error { color: #e08080; }
.pdf-spinner {
  width: 28px;
  height: 28px;
  border: 2.5px solid #555;
  border-top-color: #8ab4a0;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Thumbnails ── */
.pdf-thumbnails {
  width: 96px;
  flex-shrink: 0;
  background: #2a2a2a;
  overflow-y: auto;
  padding: 8px 6px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pdf-thumbnail {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  border: 1.5px solid transparent;
  transition: border-color 0.15s;
}
.pdf-thumbnail:hover { border-color: #6a8a7a; }
.pdf-thumbnail--active { border-color: #8ab4a0 !important; }
.pdf-thumbnail__canvas {
  width: 72px;
  height: auto;
  border-radius: 2px;
  background: #fff;
}
.pdf-thumbnail span {
  font-size: 11px;
  color: #888;
}

@media (max-width: 640px) {
  .pdf-viewer {
    min-height: 0;
    border-radius: 0;
    border-left: 0;
    border-right: 0;
  }

  .pdf-toolbar {
    height: auto;
    min-height: 44px;
    padding: 8px;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 6px;
  }

  .pdf-toolbar__left,
  .pdf-toolbar__center,
  .pdf-toolbar__right {
    flex: 1 1 100%;
    min-width: 0;
  }

  .pdf-toolbar__left {
    order: 1;
  }

  .pdf-toolbar__right {
    order: 2;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
  }

  .pdf-toolbar__center {
    order: 3;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
  }

  .pdf-toolbar__filename-text {
    max-width: calc(100vw - 104px);
  }

  .pdf-toolbar__divider {
    display: none;
  }

  .pdf-btn {
    width: 32px;
    height: 32px;
    min-width: 32px;
  }

  .pdf-page-input input {
    width: 44px;
  }

  .pdf-zoom-select {
    min-width: 68px;
  }

  .pdf-layout {
    flex-direction: column;
  }

  .pdf-body {
    padding: 12px;
  }

  .pdf-thumbnails {
    display: none;
  }

  .pdf-canvas-wrap {
    width: 100%;
  }
}
</style>
