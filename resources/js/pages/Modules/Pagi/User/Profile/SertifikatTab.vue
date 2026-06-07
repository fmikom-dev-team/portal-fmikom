<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { 
	Plus, Pencil, Trash2, Loader2, Award, Calendar, ExternalLink, 
	ChevronLeft, ChevronRight, Tags, FileText, UploadCloud, X,
	Image as ImageIcon
} from "lucide-vue-next";
import Modal from "../ui/Modal.vue";
import Progress from "../ui/Progress.vue";
import axios from "axios";

// ─── PDF.js Dynamic Loader & Thumbnail Generation ──────────────────────────
const loadPdfJs = () => {
	return new Promise<any>((resolve, reject) => {
		if ((globalThis as any).pdfjsLib) {
			resolve((globalThis as any).pdfjsLib);
			return;
		}
		const script = document.createElement('script');
		script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
		script.onload = () => {
			const pdfjs = (globalThis as any).pdfjsLib;
			// Load worker code via fetch and convert to Blob URL to bypass CORS limits
			fetch('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js')
				.then(res => res.text())
				.then(workerCode => {
					const blob = new Blob([workerCode], { type: 'application/javascript' });
					pdfjs.GlobalWorkerOptions.workerSrc = URL.createObjectURL(blob);
					resolve(pdfjs);
				})
				.catch(() => {
					// Fallback to direct CDN URL
					pdfjs.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
					resolve(pdfjs);
				});
		};
		script.onerror = reject;
		document.head.appendChild(script);
	});
};

const generatePdfThumbnail = async (file: File): Promise<Blob> => {
	const pdfjs = await loadPdfJs();
	const arrayBuffer = await file.arrayBuffer();
	const pdf = await pdfjs.getDocument({ data: arrayBuffer }).promise;
	const page = await pdf.getPage(1);
	
	const viewport = page.getViewport({ scale: 1 });
	const canvas = document.createElement('canvas');
	const context = canvas.getContext('2d');
	
	const desiredWidth = 400;
	const scale = desiredWidth / viewport.width;
	const scaledViewport = page.getViewport({ scale });
	
	canvas.width = scaledViewport.width;
	canvas.height = scaledViewport.height;
	
	if (context) {
		await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
		return new Promise<Blob>((resolve) => {
			canvas.toBlob((blob) => {
				if (blob) {
					resolve(blob);
				} else {
					canvas.toBlob((jpgBlob) => {
						resolve(jpgBlob || new Blob());
					}, 'image/jpeg', 0.8);
				}
			}, 'image/webp', 0.8);
		});
	}
	throw new Error('Canvas context not available');
};

const generatePdfThumbnailFromUrl = async (url: string): Promise<string> => {
	const pdfjs = await loadPdfJs();
	const pdf = await pdfjs.getDocument(url).promise;
	const page = await pdf.getPage(1);
	
	const viewport = page.getViewport({ scale: 1 });
	const canvas = document.createElement('canvas');
	const context = canvas.getContext('2d');
	
	const desiredWidth = 150;
	const scale = desiredWidth / viewport.width;
	const scaledViewport = page.getViewport({ scale });
	
	canvas.width = scaledViewport.width;
	canvas.height = scaledViewport.height;
	
	if (context) {
		await page.render({ canvasContext: context, viewport: scaledViewport }).promise;
		return canvas.toDataURL('image/jpeg', 0.85);
	}
	throw new Error('Canvas context not available');
};

const pdfThumbnails = ref<Record<string, string>>({});
const generatingPdfPaths = ref<Record<string, boolean>>({});

// ─── Organization Logo Imports from thesvg ─────────────────────────────────
import googleIcon from 'thesvg/google';
import microsoftIcon from 'thesvg/microsoft';
import awsIcon from 'thesvg/aws';
import courseraIcon from 'thesvg/coursera';
import udemyIcon from 'thesvg/udemy';
import ciscoIcon from 'thesvg/cisco';
import oracleIcon from 'thesvg/oracle';
import redHatIcon from 'thesvg/red-hat';
import ibmIcon from 'thesvg/ibm';
import credlyIcon from 'thesvg/credly';
import linkedinIcon from 'thesvg/linkedin';
import canvaIcon from 'thesvg/canva';

// Build data-uri: always use the colorful 'default' SVG (shown on a light background)
function toDataUri(icon: any): string {
	// Prefer: variants.default (colorful) > icon.svg > mono
	const svg = (icon.variants?.default ?? icon.svg) as string;
	return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
}

const props = defineProps<{
	isOwnProfile: boolean;
	certificates: Array<{
		id: number;
		title: string;
		issuer: string;
		date: string;
		expirationDate?: string;
		credentialId?: string;
		credentialUrl?: string;
		skills?: string[];
		media?: Array<{ name: string; path: string; type?: string; thumbnail_path?: string }>;
	}>;
	isLoading?: boolean;
}>();

const emit = defineEmits<{
	(e: 'add-toast', message: string, type: string): void;
	(e: 'update-certificates', list: any[]): void;
}>();

// Mutable local list of certificates
const localCerts = ref<any[]>([...(props.certificates || [])]);
watch(() => props.certificates, (newVal) => {
	localCerts.value = [...(newVal || [])];
}, { deep: true });

const loadMissingPdfThumbnails = () => {
	if (!localCerts.value) return;
	localCerts.value.forEach(cert => {
		if (cert.media) {
			cert.media.forEach((file: any) => {
				const isPdf = file.type === 'pdf' || file.path.endsWith('.pdf');
				if (isPdf && !file.thumbnail_path) {
					const path = file.path;
					if (pdfThumbnails.value[path] || generatingPdfPaths.value[path]) return;
					
					generatingPdfPaths.value[path] = true;
					const url = '/storage/' + path;
					generatePdfThumbnailFromUrl(url).then(dataUrl => {
						pdfThumbnails.value[path] = dataUrl;
						generatingPdfPaths.value[path] = false;
					}).catch(err => {
						console.error("Failed to generate client-side thumbnail for", path, err);
						generatingPdfPaths.value[path] = false;
					});
				}
			});
		}
	});
};

watch(localCerts, () => {
	loadMissingPdfThumbnails();
}, { deep: true, immediate: true });

// Form state - Add Modal
const showAddModal = ref(false);
const formTitle = ref('');
const formIssuer = ref('');
const formDate = ref('');
const formExpirationDate = ref('');
const formCredentialId = ref('');
const formCredentialUrl = ref('');
const formSkills = ref<string[]>([]);
const formNewMedia = ref<File[]>([]);
const formNewMediaThumbs = ref<Array<File | null>>([]);
const formNewMediaPreviews = ref<Array<{ name: string; url: string; type: string; isGenerating: boolean }>>([]);
const isSubmittingAdd = ref(false);

// Form state - Edit Modal
const showEditModal = ref(false);
const editingCertId = ref<number | null>(null);
const editTitle = ref('');
const editIssuer = ref('');
const editDate = ref('');
const editExpirationDate = ref('');
const editCredentialId = ref('');
const editCredentialUrl = ref('');
const editSkills = ref<string[]>([]);
const editExistingMedia = ref<any[]>([]);
const editNewMedia = ref<File[]>([]);
const editNewMediaThumbs = ref<Array<File | null>>([]);
const editNewMediaPreviews = ref<Array<{ name: string; url: string; type: string; isGenerating: boolean }>>([]);
const isSavingEdit = ref(false);

const uploadProgress = ref(0);
const isUploading = ref(false);

// Date Pickers State
const pickerYear = ref(new Date().getFullYear());
const skillInput = ref('');
const showSkillSuggestions = ref(false);
const addFileInput = ref<HTMLInputElement | null>(null);
const editFileInput = ref<HTMLInputElement | null>(null);
const showAddIssuePicker = ref(false);
const showAddExpiryPicker = ref(false);
const showEditIssuePicker = ref(false);
const showEditExpiryPicker = ref(false);

// Autocomplete State
const showAddAutocomplete = ref(false);
const showEditAutocomplete = ref(false);

// Logo lookup states
const formLogoUrl = ref('');
const editLogoUrl = ref('');
const showAddLogoUpload = ref(false);
const showEditLogoUpload = ref(false);
const isCheckingAddLogo = ref(false);
const isCheckingEditLogo = ref(false);
const isUploadingAddLogo = ref(false);
const isUploadingEditLogo = ref(false);

// Failed cached logos record to fall back to initials
const failedLogos = ref<Record<string, boolean>>({});

// Organization logo check (checks local POPULAR, local node_modules, and cache)
const checkOrganizationLogo = async (name: string, isEdit: boolean) => {
	const cleanName = name.trim();
	if (!cleanName) {
		if (isEdit) {
			editLogoUrl.value = '';
			showEditLogoUpload.value = false;
		} else {
			formLogoUrl.value = '';
			showAddLogoUpload.value = false;
		}
		return;
	}

	// 1. Check popular organizations client-side
	const isPopular = POPULAR_ORGANIZATIONS.some(o => o.name.toLowerCase() === cleanName.toLowerCase());
	if (isPopular) {
		const popularLogo = getOrgLogo(cleanName);
		if (isEdit) {
			editLogoUrl.value = popularLogo || '';
			showEditLogoUpload.value = false;
		} else {
			formLogoUrl.value = popularLogo || '';
			showAddLogoUpload.value = false;
		}
		return;
	}

	// 2. Query backend to check server cache & thesvg
	if (isEdit) isCheckingEditLogo.value = true;
	else isCheckingAddLogo.value = true;

	try {
		const res = await axios.get('/pagi/certificates/org-logo', {
			params: { name: cleanName }
		});

		if (res.data.success) {
			if (isEdit) {
				editLogoUrl.value = res.data.url;
				showEditLogoUpload.value = false;
			} else {
				formLogoUrl.value = res.data.url;
				showAddLogoUpload.value = false;
			}
		} else if (isEdit) {
			editLogoUrl.value = '';
			showEditLogoUpload.value = true;
		} else {
			formLogoUrl.value = '';
			showAddLogoUpload.value = true;
		}
	} catch (err) {
		console.error("Failed to check logo:", err);
		if (isEdit) {
			editLogoUrl.value = '';
			showEditLogoUpload.value = true;
		} else {
			formLogoUrl.value = '';
			showAddLogoUpload.value = true;
		}
	} finally {
		if (isEdit) isCheckingEditLogo.value = false;
		else isCheckingAddLogo.value = false;
	}
};

// Debounced watchers for input fields
let addIssuerTimeout: any = null;
watch(formIssuer, (newVal) => {
	if (addIssuerTimeout) clearTimeout(addIssuerTimeout);
	addIssuerTimeout = setTimeout(() => {
		checkOrganizationLogo(newVal, false);
	}, 500);
});

let editIssuerTimeout: any = null;
watch(editIssuer, (newVal) => {
	if (editIssuerTimeout) clearTimeout(editIssuerTimeout);
	editIssuerTimeout = setTimeout(() => {
		checkOrganizationLogo(newVal, true);
	}, 500);
});

const handleLogoUpload = async (e: Event, isEdit: boolean) => {
	const target = e.target as HTMLInputElement;
	const file = target.files?.[0];
	if (!file) return;

	const issuerName = isEdit ? editIssuer.value : formIssuer.value;
	if (!issuerName.trim()) {
		emit('add-toast', 'Please type the organization name first.', 'error');
		target.value = '';
		return;
	}

	const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
	if (!allowedMimes.includes(file.type)) {
		emit('add-toast', 'Format logo tidak valid. Gunakan JPEG, PNG, WebP, GIF, atau SVG.', 'error');
		target.value = '';
		return;
	}

	if (file.size > 2 * 1024 * 1024) {
		emit('add-toast', 'Ukuran logo maksimal adalah 2MB.', 'error');
		target.value = '';
		return;
	}

	const formData = new FormData();
	formData.append('name', issuerName.trim());
	formData.append('logo', file);

	if (isEdit) isUploadingEditLogo.value = true;
	else isUploadingAddLogo.value = true;

	try {
		const res = await axios.post('/pagi/certificates/org-logo', formData, {
			headers: { 'Content-Type': 'multipart/form-data' }
		});

		if (res.data.success) {
			if (isEdit) {
				editLogoUrl.value = res.data.url;
				failedLogos.value[issuerName] = false; // reset failure state if any
			} else {
				formLogoUrl.value = res.data.url;
			}
			emit('add-toast', 'Logo successfully uploaded and cached!', 'success');
		}
	} catch (err: any) {
		emit('add-toast', err.response?.data?.message || 'Failed to upload logo.', 'error');
	} finally {
		if (isEdit) isUploadingEditLogo.value = false;
		else isUploadingAddLogo.value = false;
		target.value = '';
	}
};

// Curated popular issuing organizations with thesvg logos (full-color, authentic)
const POPULAR_ORGANIZATIONS = [
	{ name: "Google",                       hex: '#4285F4', logo: toDataUri(googleIcon) },
	{ name: "Microsoft",                    hex: '#00A4EF', logo: toDataUri(microsoftIcon) },
	{ name: "Amazon Web Services (AWS)",    hex: '#232F3E', logo: toDataUri(awsIcon) },
	{ name: "Coursera",                     hex: '#0056D2', logo: toDataUri(courseraIcon) },
	{ name: "Udemy",                        hex: '#A435F0', logo: toDataUri(udemyIcon) },
	{ name: "Cisco",                        hex: '#1BA0D7', logo: toDataUri(ciscoIcon) },
	{ name: "Oracle",                       hex: '#F80000', logo: toDataUri(oracleIcon) },
	{ name: "Red Hat",                      hex: '#EE0000', logo: toDataUri(redHatIcon) },
	{ name: "IBM",                          hex: '#1F70C1', logo: toDataUri(ibmIcon) },
	{ name: "LinkedIn",                     hex: '#0A66C2', logo: toDataUri(linkedinIcon) },
	{ name: "Credly",                       hex: '#FF6B00', logo: toDataUri(credlyIcon) },
	{ name: "Canva",                        hex: '#00C4CC', logo: toDataUri(canvaIcon) },
	{ name: "BAN-PT",                       hex: '#1a56db', logo: null },
	{ name: "FMIKOM Academy",               hex: '#7c3aed', logo: null },
];

// Helper: find logo/hex for a given issuer name (used in cert card display)
const getOrgLogo = (issuerName: string): string | null => {
	if (!issuerName) return null;
	const lc = issuerName.toLowerCase().trim();
	const found = POPULAR_ORGANIZATIONS.find(o => o.name.toLowerCase() === lc);
	return found?.logo ?? null;
};

const getOrgHex = (issuerName: string): string => {
	if (!issuerName) return '#6366f1';
	const lc = issuerName.toLowerCase().trim();
	const found = POPULAR_ORGANIZATIONS.find(o => o.name.toLowerCase() === lc);
	return found?.hex ?? '#6366f1';
};

// FMIKOM Skills Suggestions
const SKILLS_SUGGESTIONS = [
	"Figma", "UI/UX Design", "Vue.js", "Laravel", "Tailwind CSS", 
	"TypeScript", "Git", "Docker", "Database Systems", "Cloud Computing",
	"Algoritma & Struktur Data", "Analisis Data", "Artificial Intelligence"
];

// Month List
const MONTHS = [
	{ name: 'January', val: '01' }, { name: 'February', val: '02' }, { name: 'March', val: '03' }, { name: 'April', val: '04' },
	{ name: 'May', val: '05' }, { name: 'June', val: '06' }, { name: 'July', val: '07' }, { name: 'August', val: '08' },
	{ name: 'September', val: '09' }, { name: 'October', val: '10' }, { name: 'November', val: '11' }, { name: 'December', val: '12' }
];

// Autocomplete computations
const filteredAddIssuers = computed(() => {
	const val = formIssuer.value.toLowerCase().trim();
	if (!val) return POPULAR_ORGANIZATIONS;
	return POPULAR_ORGANIZATIONS.filter(org => org.name.toLowerCase().includes(val));
});

const filteredEditIssuers = computed(() => {
	const val = editIssuer.value.toLowerCase().trim();
	if (!val) return POPULAR_ORGANIZATIONS;
	return POPULAR_ORGANIZATIONS.filter(org => org.name.toLowerCase().includes(val));
});

// Helper: Format Date string (e.g. 2026-01 to "January 2026")
const formatMonthYear = (dateStr: string) => {
	if (!dateStr) return '';
	if (!dateStr.includes('-')) return dateStr;
	const [year, month] = dateStr.split('-');
	const monthNames = [
		'January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December'
	];
	const mIdx = Number.parseInt(month, 10) - 1;
	return `${monthNames[mIdx] || month} ${year}`;
};

// Autocomplete Selection
const selectAddIssuer = (name: string) => {
	formIssuer.value = name;
	showAddAutocomplete.value = false;
};

const selectEditIssuer = (name: string) => {
	editIssuer.value = name;
	showEditAutocomplete.value = false;
};

// Year Picker Actions
const prevYear = () => { pickerYear.value--; };
const nextYear = () => { pickerYear.value++; };

// Date Selection
const selectAddIssueDate = (monthVal: string) => {
	formDate.value = `${pickerYear.value}-${monthVal}`;
	showAddIssuePicker.value = false;
};

const selectAddExpiryDate = (monthVal: string) => {
	formExpirationDate.value = `${pickerYear.value}-${monthVal}`;
	showAddExpiryPicker.value = false;
};

const selectEditIssueDate = (monthVal: string) => {
	editDate.value = `${pickerYear.value}-${monthVal}`;
	showEditIssuePicker.value = false;
};

const selectEditExpiryDate = (monthVal: string) => {
	editExpirationDate.value = `${pickerYear.value}-${monthVal}`;
	showEditExpiryPicker.value = false;
};

// Skills Tagging Logic
const addSkill = (skill: string) => {
	const s = skill.trim();
	if (!s) return;
	if (formSkills.value.includes(s) || editSkills.value.includes(s)) {
		skillInput.value = '';
		showSkillSuggestions.value = false;
		return;
	}
	if (showAddModal.value) {
		formSkills.value.push(s);
	} else if (showEditModal.value) {
		editSkills.value.push(s);
	}
	skillInput.value = '';
	showSkillSuggestions.value = false;
};

const removeSkill = (index: number) => {
	if (showAddModal.value) {
		formSkills.value.splice(index, 1);
	} else if (showEditModal.value) {
		editSkills.value.splice(index, 1);
	}
};

// Autocomplete list computed for skills
const filteredSkills = computed(() => {
	const val = skillInput.value.toLowerCase().trim();
	if (!val) return SKILLS_SUGGESTIONS;
	return SKILLS_SUGGESTIONS.filter(s => s.toLowerCase().includes(val));
});

// File validation logic (JPEG, PNG, WebP, PDF; Max 20MB)
const validateAndAddFile = (file: File, isEdit: boolean) => {
	const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];
	if (!allowedMimes.includes(file.type)) {
		emit('add-toast', 'Format file tidak valid. Gunakan JPEG, PNG, WebP, GIF, atau PDF.', 'error');
		return;
	}
	const maxSize = 20 * 1024 * 1024; // 20MB
	if (file.size > maxSize) {
		emit('add-toast', 'Ukuran file maksimal adalah 20MB.', 'error');
		return;
	}

	const currentCount = isEdit 
		? editExistingMedia.value.length + editNewMedia.value.length
		: formNewMedia.value.length;

	if (currentCount >= 3) {
		emit('add-toast', 'Maksimal lampiran adalah 3 file.', 'error');
		return;
	}

	const isPdf = file.type === 'application/pdf';
	const previewObj = {
		name: file.name,
		url: isPdf ? '' : URL.createObjectURL(file),
		type: isPdf ? 'pdf' : 'image',
		isGenerating: isPdf
	};

	if (isEdit) {
		editNewMedia.value.push(file);
		editNewMediaPreviews.value.push(previewObj);
		const idx = editNewMedia.value.length - 1;
		editNewMediaThumbs.value.push(null);
		
		if (isPdf) {
			generatePdfThumbnail(file).then((blob) => {
				const thumbFile = new File([blob], file.name + ".webp", { type: "image/webp" });
				editNewMediaThumbs.value[idx] = thumbFile;
				editNewMediaPreviews.value[idx].url = URL.createObjectURL(blob);
				editNewMediaPreviews.value[idx].isGenerating = false;
			}).catch((err) => {
				console.error("PDF thumbnail generation failed:", err);
				editNewMediaPreviews.value[idx].isGenerating = false;
			});
		}
	} else {
		formNewMedia.value.push(file);
		formNewMediaPreviews.value.push(previewObj);
		const idx = formNewMedia.value.length - 1;
		formNewMediaThumbs.value.push(null);

		if (isPdf) {
			generatePdfThumbnail(file).then((blob) => {
				const thumbFile = new File([blob], file.name + ".webp", { type: "image/webp" });
				formNewMediaThumbs.value[idx] = thumbFile;
				formNewMediaPreviews.value[idx].url = URL.createObjectURL(blob);
				formNewMediaPreviews.value[idx].isGenerating = false;
			}).catch((err) => {
				console.error("PDF thumbnail generation failed:", err);
				formNewMediaPreviews.value[idx].isGenerating = false;
			});
		}
	}
};

// File Drop Handlers
const handleDrop = (e: DragEvent, isEdit: boolean) => {
	e.preventDefault();
	if (e.dataTransfer?.files) {
		for (const file of e.dataTransfer.files) {
			validateAndAddFile(file, isEdit);
		}
	}
};

// File Input Change
const handleFileChange = (e: Event, isEdit: boolean) => {
	const target = e.target as HTMLInputElement;
	if (target.files) {
		for (const file of target.files) {
			validateAndAddFile(file, isEdit);
		}
	}
	target.value = ''; // Reset input element
};

// Remove file from list
const removeNewFile = (index: number, isEdit: boolean) => {
	if (isEdit) {
		const preview = editNewMediaPreviews.value[index];
		if (preview && preview.url && !preview.url.startsWith('http')) {
			URL.revokeObjectURL(preview.url);
		}
		editNewMedia.value.splice(index, 1);
		editNewMediaThumbs.value.splice(index, 1);
		editNewMediaPreviews.value.splice(index, 1);
	} else {
		const preview = formNewMediaPreviews.value[index];
		if (preview && preview.url && !preview.url.startsWith('http')) {
			URL.revokeObjectURL(preview.url);
		}
		formNewMedia.value.splice(index, 1);
		formNewMediaThumbs.value.splice(index, 1);
		formNewMediaPreviews.value.splice(index, 1);
	}
};

const removeExistingFile = (index: number) => {
	editExistingMedia.value.splice(index, 1);
};

// CRUD Submits
const openAddModal = () => {
	formTitle.value = '';
	formIssuer.value = '';
	formLogoUrl.value = '';
	showAddLogoUpload.value = false;
	formDate.value = '';
	formExpirationDate.value = '';
	formCredentialId.value = '';
	formCredentialUrl.value = '';
	formSkills.value = [];
	formNewMedia.value = [];
	formNewMediaThumbs.value = [];
	formNewMediaPreviews.value = [];
	pickerYear.value = new Date().getFullYear();
	showAddModal.value = true;
};

const closeAddModal = () => {
	showAddModal.value = false;
};

const handleAddCertificate = async () => {
	if (!formTitle.value.trim() || !formIssuer.value.trim() || !formDate.value.trim()) {
		emit('add-toast', 'Please fill in all required fields.', 'error');
		return;
	}

	isSubmittingAdd.value = true;
	isUploading.value = true;
	uploadProgress.value = 0;

	const formData = new FormData();
	formData.append('title', formTitle.value.trim());
	formData.append('issuer', formIssuer.value.trim());
	formData.append('date', formDate.value.trim());
	formData.append('expirationDate', formExpirationDate.value.trim());
	formData.append('credentialId', formCredentialId.value.trim());
	formData.append('credentialUrl', formCredentialUrl.value.trim());
	formData.append('skills', JSON.stringify(formSkills.value));

	formNewMedia.value.forEach((file, index) => {
		formData.append(`newMedia[${index}]`, file);
		if (formNewMediaThumbs.value[index]) {
			formData.append(`newMediaThumb[${index}]`, formNewMediaThumbs.value[index]!);
		}
	});

	try {
		const res = await axios.post('/pagi/certificates', formData, {
			headers: { 'Content-Type': 'multipart/form-data' },
			onUploadProgress: (progressEvent) => {
				if (progressEvent.total) {
					uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
				}
			}
		});
		if (res.data.success) {
			localCerts.value = res.data.certificates;
			emit('update-certificates', res.data.certificates);
			emit('add-toast', 'Certificate uploaded successfully!', 'success');
			isSubmittingAdd.value = false;
			closeAddModal();
		}
	} catch (err: any) {
		emit('add-toast', err.response?.data?.message || 'Failed to upload certificate.', 'error');
	} finally {
		isSubmittingAdd.value = false;
		isUploading.value = false;
		uploadProgress.value = 0;
	}
};

const openEditModal = (cert: any) => {
	editingCertId.value = cert.id;
	editTitle.value = cert.title || '';
	editIssuer.value = cert.issuer || '';
	editLogoUrl.value = cert.logo_url || '';
	showEditLogoUpload.value = false;
	if (!cert.logo_url && cert.issuer) {
		checkOrganizationLogo(cert.issuer, true);
	}
	editDate.value = cert.date || '';
	editExpirationDate.value = cert.expirationDate || '';
	editCredentialId.value = cert.credentialId || '';
	editCredentialUrl.value = cert.credentialUrl || '';
	editSkills.value = [...(cert.skills || [])];
	editExistingMedia.value = [...(cert.media || [])];
	editNewMedia.value = [];
	editNewMediaThumbs.value = [];
	editNewMediaPreviews.value = [];
	pickerYear.value = cert.date && cert.date.includes('-') 
		? Number.parseInt(cert.date.split('-')[0], 10) 
		: new Date().getFullYear();
	showEditModal.value = true;
};

const closeEditModal = () => {
	showEditModal.value = false;
};

const handleSaveEdit = async () => {
	if (!editingCertId.value) return;
	if (!editTitle.value.trim() || !editIssuer.value.trim() || !editDate.value.trim()) {
		emit('add-toast', 'Please fill in all required fields.', 'error');
		return;
	}

	isSavingEdit.value = true;
	isUploading.value = true;
	uploadProgress.value = 0;

	const formData = new FormData();
	// Method spoofing for PUT since FormData doesn't support PUT natively in some PHP specs
	formData.append('_method', 'PUT');
	formData.append('title', editTitle.value.trim());
	formData.append('issuer', editIssuer.value.trim());
	formData.append('date', editDate.value.trim());
	formData.append('expirationDate', editExpirationDate.value.trim());
	formData.append('credentialId', editCredentialId.value.trim());
	formData.append('credentialUrl', editCredentialUrl.value.trim());
	formData.append('skills', JSON.stringify(editSkills.value));
	formData.append('existingMedia', JSON.stringify(editExistingMedia.value));

	editNewMedia.value.forEach((file, index) => {
		formData.append(`newMedia[${index}]`, file);
		if (editNewMediaThumbs.value[index]) {
			formData.append(`newMediaThumb[${index}]`, editNewMediaThumbs.value[index]!);
		}
	});

	try {
		const res = await axios.post(`/pagi/certificates/${editingCertId.value}`, formData, {
			headers: { 'Content-Type': 'multipart/form-data' },
			onUploadProgress: (progressEvent) => {
				if (progressEvent.total) {
					uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
				}
			}
		});
		if (res.data.success) {
			localCerts.value = res.data.certificates;
			emit('update-certificates', res.data.certificates);
			emit('add-toast', 'Certificate updated successfully!', 'success');
			isSavingEdit.value = false;
			closeEditModal();
		}
	} catch (err: any) {
		emit('add-toast', err.response?.data?.message || 'Failed to update certificate.', 'error');
	} finally {
		isSavingEdit.value = false;
		isUploading.value = false;
		uploadProgress.value = 0;
	}
};

const isDeletingId = ref<number | null>(null);
const handleDeleteCertificate = async (id: number, title: string) => {
	if (!confirm(`Are you sure you want to delete "${title}"?`)) return;
	isDeletingId.value = id;
	try {
		const res = await axios.delete(`/pagi/certificates/${id}`);
		if (res.data.success) {
			localCerts.value = res.data.certificates;
			emit('update-certificates', res.data.certificates);
			emit('add-toast', 'Certificate deleted successfully!', 'success');
		}
	} catch (err: any) {
		emit('add-toast', err.response?.data?.message || 'Failed to delete certificate.', 'error');
	} finally {
		isDeletingId.value = null;
	}
};

// Document Click Event Listeners to close popovers on click outside
const closeAllPopovers = (e: MouseEvent) => {
	const target = e.target as HTMLElement;
	if (!target.closest('.datepicker-container')) {
		showAddIssuePicker.value = false;
		showAddExpiryPicker.value = false;
		showEditIssuePicker.value = false;
		showEditExpiryPicker.value = false;
	}
	if (!target.closest('.autocomplete-container')) {
		showAddAutocomplete.value = false;
		showEditAutocomplete.value = false;
	}
	if (!target.closest('.skills-container')) {
		showSkillSuggestions.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', closeAllPopovers);
});

onUnmounted(() => {
	document.removeEventListener('click', closeAllPopovers);
});
</script>

<template>
	<div class="space-y-6 max-w-3xl">
		<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
			<div>
				<h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Certificates & Licenses</h3>
				<p class="text-xs text-slate-500 mt-0.5">Professional certifications and academic achievements.</p>
			</div>
			<button 
				v-if="isOwnProfile" 
				@click="openAddModal" 
				class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-[#18181b] hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-950 text-xs font-semibold transition-all duration-300 shadow-sm hover:scale-[1.02] active:scale-[0.98] cursor-pointer border-none shrink-0 w-full sm:w-auto justify-center"
			>
				<Plus class="w-3.5 h-3.5" />
				<span>Upload Certificate</span>
			</button>
		</div>
		
		<div class="grid grid-cols-1 gap-4">
			<!-- Skeletons -->
			<template v-if="isLoading">
				<div 
					v-for="n in 2" 
					:key="n" 
					class="flex items-center gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl animate-pulse"
				>
					<div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-800 shrink-0"></div>
					<div class="flex-1 space-y-2">
						<div class="h-4 w-48 bg-slate-200 dark:bg-slate-800 rounded"></div>
						<div class="h-3 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div>
						<div class="h-2.5 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div>
					</div>
				</div>
			</template>
			
			<template v-else-if="localCerts && localCerts.length > 0">
				<div 
					v-for="cert in localCerts" 
					:key="cert.id" 
					class="flex flex-col gap-3.5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-805 p-5 rounded-2xl shadow-2xs hover:shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all group/card"
				>
					<div class="flex items-start justify-between gap-4">
						<div class="flex items-start gap-4 min-w-0 flex-1">
							<!-- Org logo: show brand logo or fallback initials -->
							<div class="w-12 h-12 rounded-xl flex items-center justify-center border border-slate-100/80 dark:border-slate-800 shrink-0 mt-0.5 overflow-hidden bg-white dark:bg-slate-800 shadow-xs p-1.5">
								<template v-if="getOrgLogo(cert.issuer)">
									<img 
										:src="getOrgLogo(cert.issuer) || undefined" 
										:alt="cert.issuer"
										class="w-full h-full object-contain"
									/>
								</template>
								<template v-else-if="cert.logo_url && !failedLogos[cert.issuer]">
									<img 
										:src="cert.logo_url" 
										:alt="cert.issuer"
										@error="failedLogos[cert.issuer] = true"
										class="w-full h-full object-contain"
									/>
								</template>
								<template v-else>
									<span 
										class="w-full h-full rounded-lg flex items-center justify-center text-white font-black text-xs"
										:style="{ backgroundColor: getOrgHex(cert.issuer) }"
									>{{ cert.issuer?.slice(0,2).toUpperCase() || '??' }}</span>
								</template>
							</div>
							<div class="flex-1 min-w-0">
								<h4 class="text-sm font-black text-slate-800 dark:text-white truncate flex items-center gap-2">
									<span>{{ cert.title }}</span>
									<a 
										v-if="cert.credentialUrl" 
										:href="cert.credentialUrl" 
										target="_blank" 
										rel="noopener noreferrer"
										class="text-slate-400 hover:text-indigo-500 transition-colors"
										title="Verify Credential"
									>
										<ExternalLink class="w-3.5 h-3.5" />
									</a>
								</h4>
								<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
									{{ cert.issuer }} • Issued {{ formatMonthYear(cert.date) }} 
									<span v-if="cert.expirationDate"> • Expires {{ formatMonthYear(cert.expirationDate) }}</span>
									<span v-else> • No Expiration</span>
								</p>
								<p v-if="cert.credentialId" class="text-[10px] text-slate-400 mt-1 font-semibold uppercase tracking-wider">Credential ID: {{ cert.credentialId }}</p>
							</div>
						</div>

						<!-- Actions -->
						<div v-if="isOwnProfile" class="flex items-center gap-1 shrink-0">
							<button 
								@click="openEditModal(cert)"
								class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center justify-center transition-colors border-none bg-transparent cursor-pointer"
								title="Edit Certificate"
							>
								<Pencil class="w-3.5 h-3.5" />
							</button>
							<button 
								@click="handleDeleteCertificate(cert.id, cert.title)"
								:disabled="isDeletingId === cert.id"
								class="w-8 h-8 rounded-full hover:bg-red-50 dark:hover:bg-red-950/30 text-slate-500 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center transition-colors border-none bg-transparent cursor-pointer disabled:opacity-50"
								title="Delete Certificate"
							>
								<Loader2 v-if="isDeletingId === cert.id" class="w-3.5 h-3.5 animate-spin" />
								<Trash2 v-else class="w-3.5 h-3.5" />
							</button>
						</div>
					</div>

					<!-- Skills Tagged -->
					<div v-if="cert.skills && cert.skills.length > 0" class="flex flex-wrap gap-1.5 pt-1">
						<span 
							v-for="skill in cert.skills" 
							:key="skill"
							class="px-2.5 py-0.5 rounded-md bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-800 text-[10px] font-bold text-slate-650 dark:text-slate-300"
						>
							{{ skill }}
						</span>
					</div>

					<!-- Attached Documents Preview (LinkedIn-Style Cards) -->
					<div v-if="cert.media && cert.media.length > 0" class="pt-3.5 border-t border-dashed border-slate-100 dark:border-slate-800 space-y-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Media & Lampiran</p>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
							<a 
								v-for="file in cert.media" 
								:key="file.path"
								:href="'/storage/' + file.path" 
								target="_blank"
								class="group/media flex items-center gap-3 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-805 bg-slate-50/40 dark:bg-slate-900/40 hover:bg-slate-50/90 dark:hover:bg-slate-900/90 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer shadow-3xs"
							>
								<!-- Left: Thumbnail (fixed square size) -->
								<div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-150 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-800 flex items-center justify-center relative">
									<!-- Case 1: PDF without thumbnail (e.g. old uploads) -->
									<template v-if="(file.type === 'pdf' || file.path.endsWith('.pdf')) && !file.thumbnail_path">
										<!-- Generating / Loading spinner -->
										<div v-if="generatingPdfPaths[file.path]" class="absolute inset-0 bg-slate-100 dark:bg-slate-800 animate-pulse flex items-center justify-center">
											<Loader2 class="w-4 h-4 text-indigo-500 animate-spin" />
										</div>
										
										<!-- Generated client-side thumbnail -->
										<img 
											v-else-if="pdfThumbnails[file.path]"
											:src="pdfThumbnails[file.path]" 
											:alt="file.name"
											class="w-full h-full object-cover transition-transform duration-300 group-hover/media:scale-105"
										/>
										
										<!-- Fallback if rendering failed or not started -->
										<FileText v-else class="w-5 h-5 text-indigo-500" />
									</template>
									
									<!-- Case 2: Image or PDF with thumbnail -->
									<template v-else>
										<!-- Loading skeleton for thumbnail -->
										<div class="absolute inset-0 bg-slate-100 dark:bg-slate-800 animate-pulse flex items-center justify-center media-skeleton group-hover/media:opacity-0 transition-opacity">
											<FileText class="w-4 h-4 text-slate-400" />
										</div>
										
										<img 
											:src="'/storage/' + (file.thumbnail_path || file.path)" 
											:alt="file.name"
											class="w-full h-full object-cover transition-transform duration-300 group-hover/media:scale-105"
											@load="(e) => (e.target as HTMLImageElement).previousElementSibling?.remove()"
											@error="(e) => { (e.target as HTMLImageElement).style.display = 'none'; }"
										/>
									</template>
								</div>
								
								<!-- Right: File Details -->
								<div class="flex-1 min-w-0 pr-1">
									<p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate group-hover/media:text-indigo-600 dark:group-hover/media:text-indigo-400 transition-colors">
										{{ file.name }}
									</p>
									<p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
										<span class="px-1 py-0.25 rounded bg-slate-100 dark:bg-slate-800 text-[8px] font-black">{{ file.type || (file.path.endsWith('.pdf') ? 'pdf' : 'image') }}</span>
										<span>Buka Dokumen</span>
									</p>
								</div>
								
								<!-- Action Icon -->
								<ExternalLink class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600 shrink-0 mr-1 transition-colors group-hover/media:text-indigo-500" />
							</a>
						</div>
					</div>
				</div>
			</template>
			
			<template v-else>
				<!-- Empty state -->
				<div class="border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center bg-slate-50/50 dark:bg-slate-900/10">
					<Award class="w-8 h-8 text-slate-400 mx-auto mb-3" />
					<h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">No Certificates Added</h3>
					<p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">Showcase your verified achievements and credentials on your profile.</p>
				</div>
			</template>
		</div>

		<!-- Add Certificate Modal -->
		<Modal :show="showAddModal" title="Upload Certificate" max-width="lg" :prevent-close="isSubmittingAdd" @close="closeAddModal">
			<form @submit.prevent="handleAddCertificate" class="space-y-4">
				<!-- Uploading progress overlay (Horizontal bar) -->
				<Transition
					enter-active-class="transition duration-300 ease-out"
					enter-from-class="opacity-0"
					enter-to-class="opacity-100"
					leave-active-class="transition duration-200 ease-in"
					leave-from-class="opacity-100"
					leave-to-class="opacity-0"
				>
					<div v-if="isUploading" class="absolute inset-0 z-50 bg-white/95 dark:bg-slate-900/95 flex flex-col items-center justify-center p-8 rounded-[24px] text-center space-y-4">
						<div class="w-full max-w-xs space-y-4">
							<h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Mengunggah Sertifikat</h3>
							<Progress :value="uploadProgress" className="w-full h-2 bg-slate-100 dark:bg-slate-800" indicatorClassName="bg-indigo-600 dark:bg-indigo-500" />
							<div class="flex justify-between items-center text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
								<span class="animate-pulse">Mengirim file...</span>
								<span>{{ uploadProgress }}%</span>
							</div>
						</div>
					</div>
				</Transition>

				<div class="space-y-1.5">
					<label for="cert-add-title" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Certificate Title <span class="text-red-500">*</span></label>
					<input 
						id="cert-add-title"
						v-model="formTitle" 
						type="text" 
						required
						placeholder="e.g. AWS Certified Solutions Architect" 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>
				
				<!-- Autocomplete Organization Input -->
				<div class="space-y-1.5 relative autocomplete-container">
					<div class="flex justify-between items-center">
						<label for="cert-add-issuer" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issuing Organization <span class="text-red-500">*</span></label>
						<!-- Tiny loading spinner or logo preview -->
						<div v-if="isCheckingAddLogo" class="flex items-center gap-1.5 text-[9px] text-slate-400">
							<Loader2 class="w-3 h-3 animate-spin text-indigo-500" />
							<span>Checking logo...</span>
						</div>
						<div v-else-if="formLogoUrl" class="flex items-center gap-1.5">
							<img :src="formLogoUrl" class="w-4 h-4 object-contain rounded" alt="Organization logo" />
							<span class="text-[9px] text-emerald-500 font-bold">Logo detected</span>
						</div>
					</div>
					<input 
						id="cert-add-issuer"
						v-model="formIssuer" 
						type="text" 
						required
						@focus="showAddAutocomplete = true"
						@click.stop="showAddAutocomplete = true"
						placeholder="e.g. Amazon Web Services, Microsoft, Google" 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
					<!-- Inline Upload Logo Option if not found and user types something custom -->
					<div v-if="showAddLogoUpload && formIssuer.trim()" class="mt-1.5 p-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/10 flex items-center justify-between gap-3">
						<div class="text-[9px] text-slate-400 font-semibold leading-normal">
							Logo tidak ditemukan. Upload logo organisasi? (Opsional)
						</div>
						<label class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-55 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold cursor-pointer hover:bg-indigo-100/50 dark:hover:bg-indigo-900/60 transition-colors border border-indigo-100/10">
							<UploadCloud class="w-3 h-3" />
							<span>{{ isUploadingAddLogo ? 'Uploading...' : 'Choose Logo' }}</span>
							<input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event, false)" :disabled="isUploadingAddLogo" />
						</label>
					</div>
					<!-- Dropdown Autocomplete menu -->
					<div 
						v-if="showAddAutocomplete && filteredAddIssuers.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-48 overflow-y-auto"
					>
						<button
							v-for="org in filteredAddIssuers"
							:key="org.name"
							type="button"
							@click="selectAddIssuer(org.name)"
							class="w-full flex items-center gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/70 text-left border-none bg-transparent cursor-pointer transition-colors"
						>
							<!-- Org logo badge: white card bg, authentic brand color svg -->
							<span 
								class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden bg-white dark:bg-slate-100 border border-slate-100 dark:border-slate-200 shadow-sm p-1"
							>
								<img v-if="org.logo" :src="org.logo" :alt="org.name" class="w-full h-full object-contain" />
								<span v-else class="font-black text-[9px] leading-none" :style="{ color: org.hex }">{{ org.name.slice(0,2).toUpperCase() }}</span>
							</span>
							<span class="flex-1 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ org.name }}</span>
							<Award class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
						</button>
					</div>
				</div>

				<!-- Month-Year Custom Popover Selectors -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5 relative datepicker-container">
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issue Date <span class="text-red-500">*</span></span>
						<div 
							@click.stop="showAddIssuePicker = !showAddIssuePicker; showAddExpiryPicker = false; pickerYear = formDate ? parseInt(formDate.split('-')[0]) : new Date().getFullYear()"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 flex items-center justify-between cursor-pointer shadow-2xs"
						>
							<span class="text-xs font-semibold" :class="formDate ? 'text-slate-800 dark:text-white' : 'text-slate-400'">
								{{ formDate ? formatMonthYear(formDate) : 'Select Issue Date' }}
							</span>
							<Calendar class="w-4 h-4 text-slate-400" />
						</div>

						<!-- Custom month-year calendar popup -->
						<div 
							v-if="showAddIssuePicker" 
							class="absolute top-16 left-0 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl p-3 z-50 w-64 text-center"
						>
							<div class="flex items-center justify-between mb-3 px-1">
								<button type="button" @click.stop="prevYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronLeft class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
								<span class="text-xs font-black text-slate-800 dark:text-white">{{ pickerYear }}</span>
								<button type="button" @click.stop="nextYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronRight class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
							</div>
							<div class="grid grid-cols-3 gap-1">
								<button 
									v-for="month in MONTHS"
									:key="month.val"
									type="button"
									@click="selectAddIssueDate(month.val)"
									class="py-1.5 text-[11px] font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-none bg-transparent cursor-pointer text-slate-700 dark:text-slate-300"
								>
									{{ month.name.slice(0, 3) }}
								</button>
							</div>
						</div>
					</div>

					<div class="space-y-1.5 relative datepicker-container">
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Expiration Date (Optional)</span>
						<div 
							@click.stop="showAddExpiryPicker = !showAddExpiryPicker; showAddIssuePicker = false; pickerYear = formExpirationDate ? parseInt(formExpirationDate.split('-')[0]) : new Date().getFullYear()"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 flex items-center justify-between cursor-pointer shadow-2xs"
						>
							<span class="text-xs font-semibold" :class="formExpirationDate ? 'text-slate-800 dark:text-white' : 'text-slate-400'">
								{{ formExpirationDate ? formatMonthYear(formExpirationDate) : 'Select Expiration Date' }}
							</span>
							<Calendar class="w-4 h-4 text-slate-400" />
						</div>

						<div 
							v-if="showAddExpiryPicker" 
							class="absolute top-16 left-0 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl p-3 z-50 w-64 text-center"
						>
							<div class="flex items-center justify-between mb-3 px-1">
								<button type="button" @click.stop="prevYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronLeft class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
								<span class="text-xs font-black text-slate-800 dark:text-white">{{ pickerYear }}</span>
								<button type="button" @click.stop="nextYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronRight class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
							</div>
							<div class="grid grid-cols-3 gap-1">
								<button 
									v-for="month in MONTHS"
									:key="month.val"
									type="button"
									@click="selectAddExpiryDate(month.val)"
									class="py-1.5 text-[11px] font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-none bg-transparent cursor-pointer text-slate-700 dark:text-slate-300"
								>
									{{ month.name.slice(0, 3) }}
								</button>
							</div>
							<div class="border-t border-slate-100 dark:border-slate-800 mt-2 pt-2">
								<button 
									type="button"
									@click="formExpirationDate = ''; showAddExpiryPicker = false;"
									class="text-[10px] font-bold text-red-550 hover:underline border-none bg-transparent cursor-pointer"
								>
									Clear Expiry Date
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5">
						<label for="cert-add-cred-id" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential ID (Optional)</label>
						<input 
							id="cert-add-cred-id"
							v-model="formCredentialId" 
							type="text" 
							placeholder="e.g. AWS-12345" 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

					<div class="space-y-1.5">
						<label for="cert-add-cred-url" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential URL (Optional)</label>
						<input 
							id="cert-add-cred-url"
							v-model="formCredentialUrl" 
							type="url" 
							placeholder="e.g. https://credly.com/..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>
				</div>

				<!-- Skills Tagging Panel -->
				<div class="space-y-1.5 relative skills-container">
					<label for="cert-add-skill-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Skills & Competency (Optional)</label>
					<div class="w-full border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 rounded-xl p-2 min-h-12 flex flex-wrap gap-1.5 items-center">
						<span 
							v-for="(skill, index) in formSkills" 
							:key="skill"
							class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/20 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-100/30"
						>
							<span>{{ skill }}</span>
							<X @click="removeSkill(index)" class="w-3 h-3 hover:text-red-500 cursor-pointer" />
						</span>
						<input 
							id="cert-add-skill-input"
							v-model="skillInput" 
							type="text"
							@focus="showSkillSuggestions = true"
							@click.stop="showSkillSuggestions = true"
							@keydown.enter.prevent="addSkill(skillInput)"
							placeholder="Add skill (press Enter)..." 
							class="flex-1 min-w-[120px] bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none border-none py-1 px-1.5"
						/>
					</div>

					<!-- Skills Suggestions Menu -->
					<div 
						v-if="showSkillSuggestions && filteredSkills.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-40 overflow-y-auto"
					>
						<button
							v-for="s in filteredSkills"
							:key="s"
							type="button"
							@click="addSkill(s)"
							class="w-full flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-left border-none bg-transparent cursor-pointer"
						>
							<span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ s }}</span>
							<Tags class="w-3.5 h-3.5 text-slate-400" />
						</button>
					</div>
				</div>

				<!-- Drag and drop upload panel (Max 3 files, Max 20MB) -->
				<div class="space-y-1.5">
					<label for="cert-add-file-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Attachment (Images / PDF, Max 3 Files, Max 20MB)</label>
					<div 
						@dragover.prevent
						@drop.prevent="handleDrop($event, false)"
						@click="addFileInput?.click()"
						class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/30 dark:bg-slate-900/20 rounded-2xl p-5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center min-h-24"
					>
						<input 
							id="cert-add-file-input"
							type="file" 
							ref="addFileInput" 
							multiple
							accept="image/*,application/pdf"
							class="hidden" 
							@change="handleFileChange($event, false)"
						/>
						<UploadCloud class="w-6 h-6 text-slate-400 mb-1.5" />
						<p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
							Tarik & lepas file di sini, atau <span class="text-indigo-500 hover:underline">pilih file</span>
						</p>
						<p class="text-[9px] text-slate-400 mt-0.5">JPEG, PNG, WebP, GIF, PDF (Max. 20MB per file)</p>
					</div>

					<!-- Uploaded Files List with Visual Previews -->
					<div v-if="formNewMediaPreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
						<div 
							v-for="(preview, index) in formNewMediaPreviews" 
							:key="index"
							class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
						>
							<!-- Skeleton/Generating Loader -->
							<div v-if="preview.isGenerating" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 animate-pulse p-2">
								<Loader2 class="w-5 h-5 text-indigo-500 animate-spin mb-1" />
								<span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Generating thumb...</span>
							</div>
							
							<template v-else>
								<!-- Preview Image -->
								<img 
									v-if="preview.url" 
									:src="preview.url" 
									class="w-full h-full object-cover" 
								/>
								<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
									<FileText class="w-6 h-6 text-slate-400 mb-1" />
									<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ preview.name }}</span>
								</div>

								<!-- PDF/Image Badge overlay -->
								<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
									<FileText v-if="preview.type === 'pdf'" class="w-2 h-2 text-indigo-400" />
									<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
									<span>{{ preview.type }}</span>
								</div>

								<!-- Delete Button Overlay -->
								<button 
									type="button" 
									@click.stop="removeNewFile(index, false)"
									class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
								>
									<X class="w-3.5 h-3.5" />
								</button>
							</template>
						</div>
					</div>
				</div>

				<div class="flex items-center justify-end gap-2 pt-2">
					<button 
						type="button"
						@click="closeAddModal" 
						class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer bg-white dark:bg-slate-900"
						:disabled="isSubmittingAdd"
					>
						Cancel
					</button>
					<button 
						type="submit"
						class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#18181b] hover:bg-zinc-700 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-sm transition-all cursor-pointer border-none"
						:disabled="isSubmittingAdd"
					>
						<span v-if="isSubmittingAdd" class="flex items-center gap-1.5">
							<Loader2 class="w-3.5 h-3.5 animate-spin" /> Uploading...
						</span>
						<span v-else>Save Certificate</span>
					</button>
				</div>
			</form>
		</Modal>

		<!-- Edit Certificate Modal -->
		<Modal :show="showEditModal" title="Edit Certificate" max-width="lg" :prevent-close="isSavingEdit" @close="closeEditModal">
			<form @submit.prevent="handleSaveEdit" class="space-y-4">
				<!-- Uploading progress overlay (Horizontal bar) -->
				<Transition
					enter-active-class="transition duration-300 ease-out"
					enter-from-class="opacity-0"
					enter-to-class="opacity-100"
					leave-active-class="transition duration-200 ease-in"
					leave-from-class="opacity-100"
					leave-to-class="opacity-0"
				>
					<div v-if="isUploading" class="absolute inset-0 z-50 bg-white/95 dark:bg-slate-900/95 flex flex-col items-center justify-center p-8 rounded-[24px] text-center space-y-4">
						<div class="w-full max-w-xs space-y-4">
							<h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Mengubah Sertifikat</h3>
							<Progress :value="uploadProgress" className="w-full h-2 bg-slate-100 dark:bg-slate-800" indicatorClassName="bg-indigo-600 dark:bg-indigo-500" />
							<div class="flex justify-between items-center text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
								<span class="animate-pulse">Mengirim file...</span>
								<span>{{ uploadProgress }}%</span>
							</div>
						</div>
					</div>
				</Transition>

				<div class="space-y-1.5">
					<label for="cert-edit-title" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Certificate Title <span class="text-red-500">*</span></label>
					<input 
						id="cert-edit-title"
						v-model="editTitle" 
						type="text" 
						required
						placeholder="Certificate Title..." 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
				</div>
				
				<div class="space-y-1.5 relative autocomplete-container">
					<div class="flex justify-between items-center">
						<label for="cert-edit-issuer" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issuing Organization <span class="text-red-500">*</span></label>
						<!-- Tiny loading spinner or logo preview -->
						<div v-if="isCheckingEditLogo" class="flex items-center gap-1.5 text-[9px] text-slate-400">
							<Loader2 class="w-3 h-3 animate-spin text-indigo-500" />
							<span>Checking logo...</span>
						</div>
						<div v-else-if="editLogoUrl" class="flex items-center gap-1.5">
							<img :src="editLogoUrl" alt="Logo preview" class="w-4 h-4 object-contain rounded" />
							<span class="text-[9px] text-emerald-500 font-bold">Logo detected</span>
						</div>
					</div>
					<input 
						id="cert-edit-issuer"
						v-model="editIssuer" 
						type="text" 
						required
						@focus="showEditAutocomplete = true"
						@click.stop="showEditAutocomplete = true"
						placeholder="e.g. Coursera..." 
						class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
					/>
					<!-- Inline Upload Logo Option if not found and user types something custom -->
					<div v-if="showEditLogoUpload && editIssuer.trim()" class="mt-1.5 p-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/10 flex items-center justify-between gap-3">
						<div class="text-[9px] text-slate-400 font-semibold leading-normal">
							Logo tidak ditemukan. Upload logo organisasi? (Opsional)
						</div>
						<label class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-55 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold cursor-pointer hover:bg-indigo-100/50 dark:hover:bg-indigo-900/60 transition-colors border border-indigo-100/10">
							<UploadCloud class="w-3 h-3" />
							<span>{{ isUploadingEditLogo ? 'Uploading...' : 'Choose Logo' }}</span>
							<input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event, true)" :disabled="isUploadingEditLogo" />
						</label>
					</div>
					<div 
						v-if="showEditAutocomplete && filteredEditIssuers.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-48 overflow-y-auto"
					>
						<button
							v-for="org in filteredEditIssuers"
							:key="org.name"
							type="button"
							@click="selectEditIssuer(org.name)"
							class="w-full flex items-center gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/70 text-left border-none bg-transparent cursor-pointer transition-colors"
						>
							<!-- Org logo badge: white card bg, authentic brand color svg -->
							<span 
								class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden bg-white dark:bg-slate-100 border border-slate-100 dark:border-slate-200 shadow-sm p-1"
							>
								<img v-if="org.logo" :src="org.logo" :alt="org.name" class="w-full h-full object-contain" />
								<span v-else class="font-black text-[9px] leading-none" :style="{ color: org.hex }">{{ org.name.slice(0,2).toUpperCase() }}</span>
							</span>
							<span class="flex-1 text-xs font-semibold text-slate-800 dark:text-slate-200">{{ org.name }}</span>
							<Award class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
						</button>
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5 relative datepicker-container">
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Issue Date <span class="text-red-500">*</span></span>
						<div 
							@click.stop="showEditIssuePicker = !showEditIssuePicker; showEditExpiryPicker = false; pickerYear = editDate ? parseInt(editDate.split('-')[0]) : new Date().getFullYear()"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 flex items-center justify-between cursor-pointer shadow-2xs"
						>
							<span class="text-xs font-semibold" :class="editDate ? 'text-slate-800 dark:text-white' : 'text-slate-400'">
								{{ editDate ? formatMonthYear(editDate) : 'Select Issue Date' }}
							</span>
							<Calendar class="w-4 h-4 text-slate-400" />
						</div>

						<div 
							v-if="showEditIssuePicker" 
							class="absolute top-16 left-0 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl p-3 z-50 w-64 text-center"
						>
							<div class="flex items-center justify-between mb-3 px-1">
								<button type="button" @click.stop="prevYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronLeft class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
								<span class="text-xs font-black text-slate-800 dark:text-white">{{ pickerYear }}</span>
								<button type="button" @click.stop="nextYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronRight class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
							</div>
							<div class="grid grid-cols-3 gap-1">
								<button 
									v-for="month in MONTHS"
									:key="month.val"
									type="button"
									@click="selectEditIssueDate(month.val)"
									class="py-1.5 text-[11px] font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-none bg-transparent cursor-pointer text-slate-700 dark:text-slate-300"
								>
									{{ month.name.slice(0, 3) }}
								</button>
							</div>
						</div>
					</div>

					<div class="space-y-1.5 relative datepicker-container">
						<span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Expiration Date (Optional)</span>
						<div 
							@click.stop="showEditExpiryPicker = !showEditExpiryPicker; showEditIssuePicker = false; pickerYear = editExpirationDate ? parseInt(editExpirationDate.split('-')[0]) : new Date().getFullYear()"
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 flex items-center justify-between cursor-pointer shadow-2xs"
						>
							<span class="text-xs font-semibold" :class="editExpirationDate ? 'text-slate-800 dark:text-white' : 'text-slate-400'">
								{{ editExpirationDate ? formatMonthYear(editExpirationDate) : 'Select Expiration Date' }}
							</span>
							<Calendar class="w-4 h-4 text-slate-400" />
						</div>

						<div 
							v-if="showEditExpiryPicker" 
							class="absolute top-16 left-0 bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl p-3 z-50 w-64 text-center"
						>
							<div class="flex items-center justify-between mb-3 px-1">
								<button type="button" @click.stop="prevYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronLeft class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
								<span class="text-xs font-black text-slate-800 dark:text-white">{{ pickerYear }}</span>
								<button type="button" @click.stop="nextYear" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent cursor-pointer">
									<ChevronRight class="w-4 h-4 text-slate-600 dark:text-slate-400" />
								</button>
							</div>
							<div class="grid grid-cols-3 gap-1">
								<button 
									v-for="month in MONTHS"
									:key="month.val"
									type="button"
									@click="selectEditExpiryDate(month.val)"
									class="py-1.5 text-[11px] font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-none bg-transparent cursor-pointer text-slate-700 dark:text-slate-300"
								>
									{{ month.name.slice(0, 3) }}
								</button>
							</div>
							<div class="border-t border-slate-100 dark:border-slate-800 mt-2 pt-2">
								<button 
									type="button"
									@click="editExpirationDate = ''; showEditExpiryPicker = false;"
									class="text-[10px] font-bold text-red-550 hover:underline border-none bg-transparent cursor-pointer"
								>
									Clear Expiry Date
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div class="space-y-1.5">
						<label for="cert-edit-cred-id" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential ID (Optional)</label>
						<input 
							id="cert-edit-cred-id"
							v-model="editCredentialId" 
							type="text" 
							placeholder="Credential ID..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>

					<div class="space-y-1.5">
						<label for="cert-edit-cred-url" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Credential URL (Optional)</label>
						<input 
							id="cert-edit-cred-url"
							v-model="editCredentialUrl" 
							type="url" 
							placeholder="Credential URL..." 
							class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-slate-800 shadow-2xs"
						/>
					</div>
				</div>

				<div class="space-y-1.5 relative skills-container">
					<label for="cert-edit-skill-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Skills & Competency (Optional)</label>
					<div class="w-full border border-slate-200 dark:border-slate-805 bg-white dark:bg-slate-900 rounded-xl p-2 min-h-12 flex flex-wrap gap-1.5 items-center">
						<span 
							v-for="(skill, index) in editSkills" 
							:key="skill"
							class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/20 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-100/30"
						>
							<span>{{ skill }}</span>
							<X @click="removeSkill(index)" class="w-3 h-3 hover:text-red-500 cursor-pointer" />
						</span>
						<input 
							id="cert-edit-skill-input"
							v-model="skillInput" 
							type="text"
							@focus="showSkillSuggestions = true"
							@click.stop="showSkillSuggestions = true"
							@keydown.enter.prevent="addSkill(skillInput)"
							placeholder="Add skill (press Enter)..." 
							class="flex-1 min-w-[120px] bg-transparent text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none border-none py-1 px-1.5"
						/>
					</div>

					<div 
						v-if="showSkillSuggestions && filteredSkills.length > 0"
						class="absolute top-16 left-0 w-full bg-white dark:bg-slate-905 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-xl py-1 z-50 max-h-40 overflow-y-auto"
					>
						<button
							v-for="s in filteredSkills"
							:key="s"
							type="button"
							@click="addSkill(s)"
							class="w-full flex items-center justify-between px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-left border-none bg-transparent cursor-pointer"
						>
							<span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ s }}</span>
							<Tags class="w-3.5 h-3.5 text-slate-400" />
						</button>
					</div>
				</div>

				<!-- Attached Files -->
				<div class="space-y-1.5">
					<label for="cert-edit-file-input" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Attachment (Images / PDF, Max 3 Files, Max 20MB)</label>
					
					<!-- Existing Files (Visual Preview Grid) -->
					<div v-if="editExistingMedia.length > 0" class="space-y-2 pb-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">File Terunggah (Uploaded Files):</p>
						<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
							<div 
								v-for="(file, index) in editExistingMedia" 
								:key="file.path"
								class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
							>
								<!-- Case 1: PDF without thumbnail (e.g. old uploads) -->
								<template v-if="(file.type === 'pdf' || file.path.endsWith('.pdf')) && !file.thumbnail_path">
									<!-- Generating / Loading spinner -->
									<div v-if="generatingPdfPaths[file.path]" class="absolute inset-0 bg-slate-105 dark:bg-slate-800 animate-pulse flex flex-col items-center justify-center p-2">
										<Loader2 class="w-5 h-5 text-indigo-500 animate-spin mb-1" />
										<span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Generating...</span>
									</div>
									
									<!-- Generated client-side thumbnail -->
									<img 
										v-else-if="pdfThumbnails[file.path]"
										:src="pdfThumbnails[file.path]" 
										alt="PDF thumbnail"
										class="w-full h-full object-cover"
									/>
									
									<!-- Fallback if rendering failed or not started -->
									<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
										<FileText class="w-6 h-6 text-slate-400 mb-1" />
										<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ file.name }}</span>
									</div>
								</template>
								
								<!-- Case 2: Image or PDF with thumbnail -->
								<template v-else>
									<img 
										:src="'/storage/' + (file.thumbnail_path || file.path)" 
										alt="Attachment preview"
										class="w-full h-full object-cover" 
									/>
								</template>

								<!-- PDF/Image Badge overlay -->
								<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
									<FileText v-if="file.type === 'pdf' || file.path.endsWith('.pdf')" class="w-2 h-2 text-indigo-400" />
									<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
									<span>{{ file.type || (file.path.endsWith('.pdf') ? 'pdf' : 'image') }}</span>
								</div>

								<!-- Delete Button Overlay -->
								<button 
									type="button" 
									@click.stop="removeExistingFile(index)"
									class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
								>
									<X class="w-3.5 h-3.5" />
								</button>
							</div>
						</div>
					</div>

					<div 
						@dragover.prevent
						@drop.prevent="handleDrop($event, true)"
						@click="editFileInput?.click()"
						class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/30 dark:bg-slate-900/20 rounded-2xl p-5 text-center cursor-pointer transition-colors flex flex-col items-center justify-center min-h-24"
					>
						<input 
							type="file" 
							id="cert-edit-file-input"
							ref="editFileInput" 
							multiple
							accept="image/*,application/pdf"
							class="hidden" 
							@change="handleFileChange($event, true)"
						/>
						<UploadCloud class="w-6 h-6 text-slate-400 mb-1.5" />
						<p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
							Tarik & lepas file baru di sini, atau <span class="text-indigo-500 hover:underline">pilih file</span>
						</p>
						<p class="text-[9px] text-slate-400 mt-0.5">JPEG, PNG, WebP, GIF, PDF (Max. 20MB per file)</p>
					</div>

					<!-- New Files List with Visual Previews -->
					<div v-if="editNewMediaPreviews.length > 0" class="space-y-2 pt-2">
						<p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">File Baru Ditambahkan:</p>
						<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
							<div 
								v-for="(preview, index) in editNewMediaPreviews" 
								:key="index"
								class="group relative aspect-video rounded-xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all"
							>
								<!-- Skeleton/Generating Loader -->
								<div v-if="preview.isGenerating" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 animate-pulse p-2">
									<Loader2 class="w-5 h-5 text-indigo-500 animate-spin mb-1" />
									<span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Generating thumb...</span>
								</div>
								
								<template v-else>
									<!-- Preview Image -->
									<img 
										v-if="preview.url" 
										:src="preview.url" 
										alt="New attachment preview"
										class="w-full h-full object-cover" 
									/>
									<div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-105 dark:bg-slate-800 p-2">
										<FileText class="w-6 h-6 text-slate-400 mb-1" />
										<span class="text-[9px] text-slate-500 truncate max-w-full font-semibold">{{ preview.name }}</span>
									</div>

									<!-- PDF/Image Badge overlay -->
									<div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/60 backdrop-blur-xs text-[7px] font-black text-white uppercase tracking-wider flex items-center gap-0.5">
										<FileText v-if="preview.type === 'pdf'" class="w-2 h-2 text-indigo-400" />
										<ImageIcon v-else class="w-2 h-2 text-emerald-400" />
										<span>{{ preview.type }}</span>
									</div>

									<!-- Delete Button Overlay -->
									<button 
										type="button" 
										@click.stop="removeNewFile(index, true)"
										class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity border-none cursor-pointer duration-200"
									>
										<X class="w-3.5 h-3.5" />
									</button>
								</template>
							</div>
						</div>
					</div>
				</div>

				<div class="flex items-center justify-end gap-2 pt-2">
					<button 
						type="button"
						@click="closeEditModal" 
						class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer bg-white dark:bg-slate-900"
						:disabled="isSavingEdit"
					>
						Cancel
					</button>
					<button 
						type="submit"
						class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#18181b] hover:bg-zinc-700 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-sm transition-all cursor-pointer border-none"
						:disabled="isSavingEdit"
					>
						<span v-if="isSavingEdit" class="flex items-center gap-1.5">
							<Loader2 class="w-3.5 h-3.5 animate-spin" /> Saving...
						</span>
						<span v-else>Save Changes</span>
					</button>
				</div>
			</form>
		</Modal>
	</div>
</template>
