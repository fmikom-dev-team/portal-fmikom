<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Building2,
    Camera,
    CheckCircle2,
    Clock3,
    ImagePlus,
    LoaderCircle,
    LocateFixed,
    LogOut,
    Navigation,
    Sparkles,
} from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import StudentLayout from '@/layouts/Modules/Wims/Mahasiswa/Layout.vue';
import absensiRoutes from '@/routes/wims/absensi';

defineOptions({
    layout: StudentLayout,
});

type FlashProps = {
    success?: string;
    distance?: string | number | null;
    is_valid?: boolean | string | null;
    error?: string | null;
};

type AttendanceStatus =
    | 'not_checked_in'
    | 'checked_in'
    | 'checked_out'
    | 'excused_absence';

type AbsenceRequestItem = {
    id: number;
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    tanggal_label?: string | null;
    jenis?: string | null;
    alasan?: string | null;
    status?: string | null;
    catatan_mitra?: string | null;
    can_cancel?: boolean;
    bukti_url?: string | null;
};

type AttendanceProps = {
    pendaftaran_id?: number | null;
    company_name?: string | null;
    radius?: number | null;
    office_latitude?: number | string | null;
    office_longitude?: number | string | null;
    status?: AttendanceStatus | null;
    check_in_time?: string | null;
    check_out_time?: string | null;
    check_in_photo_url?: string | null;
    check_out_photo_url?: string | null;
    can_check_in?: boolean | null;
    can_check_out?: boolean | null;
    can_attend_today?: boolean | null;
    workday_message?: string | null;
    is_late?: boolean | null;
    current_time?: string | null;
    location_status?: string | null;
    absensi_hari_ini?: {
        masuk?: string | null;
        keluar?: string | null;
    } | null;
    today_absence?: {
        jenis?: string | null;
        status?: string | null;
        alasan?: string | null;
        tanggal_label?: string | null;
    } | null;
    history_count?: number | null;
    history_download_url?: string | null;
    current_period_history_download_url?: string | null;
    absence_requests?: AbsenceRequestItem[];
};

type PageProps = {
    flash?: FlashProps;
    errors?: Record<string, string | undefined>;
    attendance?: AttendanceProps;
};

const page = usePage<PageProps>();
const photoInput = ref<HTMLInputElement | null>(null);
const videoPreview = ref<HTMLVideoElement | null>(null);
const timeInterval = ref<number | null>(null);
const previewUrl = ref<string | null>(null);
const locationState = ref<'idle' | 'loading' | 'success' | 'error'>('idle');
const locationError = ref('');
const locationAccuracy = ref<number | null>(null);
const cameraState = ref<'idle' | 'loading' | 'ready' | 'error'>('idle');
const cameraError = ref('');
const cameraStream = ref<MediaStream | null>(null);
const cameraFrameReady = ref(false);
const cameraRequestId = ref(0);
const maxPhotoSizeBytes = 5 * 1024 * 1024;
const maxPhotoSizeLabel = '5 MB';
const absencePanelOpen = ref(false);

const form = useForm({
    pendaftaran_id: null as number | null,
    latitude: null as number | null,
    longitude: null as number | null,
    photo: null as File | null,
});

const absenceForm = useForm({
    pendaftaran_id: null as number | null,
    tanggal_mulai: '',
    tanggal_selesai: '',
    jenis: 'izin',
    alasan: '',
    bukti: null as File | null,
});

const flash = computed(() => page.props.flash ?? {});
const attendance = computed(() => page.props.attendance ?? {});
const pageErrors = computed(() => page.props.errors ?? {});
const currentTime = ref(
    attendance.value.current_time
        ? new Date(attendance.value.current_time)
        : new Date(),
);

const attendanceStatus = computed<AttendanceStatus>(
    () => attendance.value.status ?? 'not_checked_in',
);
const checkedInAt = computed(() => attendance.value.check_in_time ?? null);
const checkedOutAt = computed(() => attendance.value.check_out_time ?? null);
const canCheckIn = computed(() => Boolean(attendance.value.can_check_in));
const canCheckOut = computed(() => Boolean(attendance.value.can_check_out));
const canAttendToday = computed(
    () => attendance.value.can_attend_today !== false,
);
const serverLocationStatus = computed(
    () => attendance.value.location_status ?? null,
);
const officeLatitude = computed(() =>
    Number(attendance.value.office_latitude ?? NaN),
);
const officeLongitude = computed(() =>
    Number(attendance.value.office_longitude ?? NaN),
);
const attendanceRadius = computed(() => Number(attendance.value.radius ?? NaN));
const todayAbsence = computed(() => attendance.value.today_absence ?? null);
const absenceRequests = computed(() => attendance.value.absence_requests ?? []);

const globalError = computed(
    () =>
        flash.value.error ||
        pageErrors.value.absen ||
        pageErrors.value.location ||
        (!canAttendToday.value
            ? attendance.value.workday_message
            : undefined) ||
        pageErrors.value.pendaftaran_id ||
        (!attendance.value.pendaftaran_id
            ? 'Data pendaftaran magang Anda belum tersedia.'
            : undefined),
);

watch(
    () => flash.value.success,
    (val) => {
        if (val) {
            form.reset('latitude', 'longitude', 'photo');
            form.pendaftaran_id = attendance.value.pendaftaran_id ?? null;
            absenceForm.reset(
                'tanggal_mulai',
                'tanggal_selesai',
                'jenis',
                'alasan',
                'bukti',
            );
            absenceForm.jenis = 'izin';
            absenceForm.pendaftaran_id =
                attendance.value.pendaftaran_id ?? null;
            resetPhotoState();
            locationState.value = 'idle';
            locationAccuracy.value = null;
        }
    },
);

watch(
    attendance,
    (value) => {
        form.pendaftaran_id = value.pendaftaran_id ?? null;
        absenceForm.pendaftaran_id = value.pendaftaran_id ?? null;
    },
    { immediate: true },
);

watch(
    () => attendance.value.current_time,
    (value) => {
        if (value) {
            currentTime.value = new Date(value);
        }
    },
    { immediate: true },
);

const canSubmit = computed(
    () =>
        !!attendance.value.pendaftaran_id &&
        locationState.value === 'success' &&
        locationValidationState.value === 'inside' &&
        !!form.photo &&
        !form.processing,
);

const checkout = () => {
    form.post('/wims/absensi/checkout', {
        forceFormData: true,
    });
};

const formatAttendanceTime = (value?: string | null) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const statusLabel = computed(() =>
    attendanceStatus.value === 'checked_out'
        ? 'Sudah Check-out'
        : attendanceStatus.value === 'checked_in'
          ? 'Sudah Check-in'
          : attendanceStatus.value === 'excused_absence'
            ? 'Izin / Sakit Disetujui'
            : 'Belum Presensi',
);

const statusClasses = computed(() =>
    attendanceStatus.value === 'checked_out'
        ? 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'
        : attendanceStatus.value === 'checked_in'
          ? 'border-sky-200 bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300'
          : attendanceStatus.value === 'excused_absence'
            ? 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300'
            : 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300',
);

const formattedTime = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(currentTime.value),
);

const formattedDate = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(currentTime.value),
);

const calculateDistanceInMeters = (
    lat1: number,
    lng1: number,
    lat2: number,
    lng2: number,
) => {
    // Frontend menghitung estimasi jarak yang sama secara lokal agar mahasiswa
    // mendapat umpan balik cepat sebelum request divalidasi ulang di backend.
    const earthRadius = 6371000;
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLng = ((lng2 - lng1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLng / 2) *
            Math.sin(dLng / 2);

    return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
};

const locationDistance = computed(() => {
    if (
        form.latitude === null ||
        form.longitude === null ||
        Number.isNaN(officeLatitude.value) ||
        Number.isNaN(officeLongitude.value)
    ) {
        return null;
    }

    return calculateDistanceInMeters(
        form.latitude,
        form.longitude,
        officeLatitude.value,
        officeLongitude.value,
    );
});

const locationValidationState = computed<
    'inside' | 'outside' | 'unknown' | 'missing-office'
>(() => {
    if (
        Number.isNaN(officeLatitude.value) ||
        Number.isNaN(officeLongitude.value) ||
        Number.isNaN(attendanceRadius.value)
    ) {
        return 'missing-office';
    }

    if (locationState.value !== 'success' || locationDistance.value === null) {
        return 'unknown';
    }

    // Tombol aksi hanya aktif bila posisi mahasiswa berada di dalam radius geofence perusahaan.
    return locationDistance.value <= attendanceRadius.value
        ? 'inside'
        : 'outside';
});

const isOutsideAttendanceArea = computed(
    () => locationValidationState.value === 'outside',
);

const locationDistanceLabel = computed(() =>
    locationDistance.value === null
        ? '-'
        : `${Math.round(locationDistance.value)} m`,
);

const locationAccuracyThreshold = computed(() => {
    if (Number.isNaN(attendanceRadius.value) || attendanceRadius.value <= 0) {
        return 100;
    }

    return Math.max(25, Math.min(attendanceRadius.value, 150));
});

const locationAccuracyLabel = computed(() =>
    locationAccuracy.value === null
        ? '-'
        : `±${Math.round(locationAccuracy.value)} m`,
);

const locationStatusLabel = computed(() =>
    locationValidationState.value === 'outside'
        ? 'Di luar area'
        : locationValidationState.value === 'inside'
          ? 'Dalam area'
          : serverLocationStatus.value === 'valid'
            ? 'Valid'
            : serverLocationStatus.value === 'invalid'
              ? 'Di luar area'
              : locationState.value === 'success'
                ? 'Siap'
                : locationState.value === 'loading'
                  ? 'Memuat'
                  : locationState.value === 'error'
                    ? 'Perlu izin'
                    : 'Belum',
);

const photoStatusLabel = computed(() =>
    form.photo
        ? 'Foto Terverifikasi'
        : cameraState.value === 'loading' || cameraState.value === 'ready'
          ? 'Foto Disiapkan'
          : 'Foto Belum Ada',
);

const isVerificationLoading = computed(
    () => locationState.value === 'loading' || cameraState.value === 'loading',
);

const canCapturePhoto = computed(
    () =>
        cameraState.value === 'ready' &&
        cameraFrameReady.value &&
        locationValidationState.value === 'inside',
);

const verificationButtonText = computed(() =>
    isVerificationLoading.value
        ? 'Menyiapkan Kamera & Lokasi...'
        : form.photo || cameraState.value === 'ready'
          ? 'Ambil Ulang Kamera & Lokasi'
          : 'Mulai Kamera & Lokasi',
);

const locationBadgeClasses = computed(() =>
    isOutsideAttendanceArea.value
        ? 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300'
        : locationValidationState.value === 'inside'
          ? 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'
          : 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300',
);

const locationBadgeLabel = computed(() =>
    locationValidationState.value === 'inside'
        ? 'Lokasi Valid'
        : locationValidationState.value === 'outside'
          ? 'Di Luar Area Presensi'
          : 'Lokasi Belum Tervalidasi',
);

const verificationSummaryMessage = computed(() => {
    if (locationState.value === 'error') {
        return locationError.value;
    }

    if (cameraState.value === 'error') {
        return cameraError.value;
    }

    if (isOutsideAttendanceArea.value) {
        return 'Anda berada di luar radius presensi. Dekati lokasi magang agar verifikasi lengkap.';
    }

    if (locationValidationState.value === 'inside' && form.photo) {
        return 'Verifikasi lokasi dan foto sudah lengkap. Presensi siap dikirim.';
    }

    if (locationValidationState.value === 'inside') {
        return 'Lokasi sudah valid. Ambil foto verifikasi untuk melanjutkan presensi.';
    }

    if (isVerificationLoading.value) {
        return 'Sistem sedang menyiapkan GPS dan kamera.';
    }

    return 'Mulai verifikasi untuk mengaktifkan GPS dan kamera dalam satu alur.';
});

const verificationSummaryClass = computed(() => {
    if (locationState.value === 'error' || cameraState.value === 'error') {
        return 'border-rose-200 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300';
    }

    if (isOutsideAttendanceArea.value) {
        return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
    }

    if (locationValidationState.value === 'inside' && form.photo) {
        return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    }

    return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
});

const isLocationReady = computed(
    () => locationValidationState.value === 'inside',
);

const isPhotoReady = computed(() => Boolean(form.photo));

const isAttendanceReady = computed(() => canSubmit.value);

const isCheckInDisabled = computed(() => !canSubmit.value || !canCheckIn.value);

const isCheckoutDisabled = computed(
    () =>
        form.processing ||
        !canCheckOut.value ||
        locationState.value !== 'success' ||
        isOutsideAttendanceArea.value ||
        !form.photo,
);

const checkInButtonText = computed(() =>
    attendanceStatus.value === 'not_checked_in'
        ? 'Presensi Sekarang'
        : attendanceStatus.value === 'excused_absence'
          ? 'Tidak Perlu Check-in Hari Ini'
          : 'Sudah Presensi Hari Ini',
);

const checkoutButtonText = computed(() =>
    attendanceStatus.value === 'checked_out'
        ? 'Sudah Check-out Hari Ini'
        : attendanceStatus.value === 'checked_in'
          ? 'Check-out Sekarang'
          : 'Check-out Belum Tersedia',
);

const actionHint = computed(() => {
    if (attendanceStatus.value === 'checked_out') {
        return 'Presensi hari ini sudah lengkap.';
    }

    if (attendanceStatus.value === 'excused_absence') {
        return 'Hari ini ditandai sebagai ketidakhadiran resmi yang sudah disetujui pembimbing mitra.';
    }

    if (!canAttendToday.value) {
        return (
            attendance.value.workday_message ||
            'Presensi hanya dapat dilakukan selama periode magang aktif.'
        );
    }

    if (locationValidationState.value === 'missing-office') {
        return 'Lokasi kantor belum lengkap. Hubungi admin sebelum melakukan presensi.';
    }

    if (isOutsideAttendanceArea.value) {
        return 'Anda berada di luar radius presensi. Mendekat ke lokasi magang sebelum tombol aksi aktif.';
    }

    if (attendanceStatus.value === 'checked_in') {
        return 'Check-in sudah tercatat. Mulai ulang kamera dan lokasi untuk menyiapkan check-out.';
    }

    return 'Mulai kamera dan lokasi terlebih dahulu. Foto hanya bisa diambil saat posisi Anda berada dalam radius presensi.';
});

const absenceKindLabel = (value?: string | null) => {
    if (value === 'sakit') {
        return 'Sakit';
    }

    return 'Izin';
};

const absenceStatusLabel = (value?: string | null) => {
    if (value === 'approved') {
        return 'Disetujui';
    }

    if (value === 'rejected') {
        return 'Ditolak';
    }

    if (value === 'cancelled') {
        return 'Dibatalkan';
    }

    return 'Pending';
};

const absenceStatusClass = (value?: string | null) => {
    if (value === 'approved') {
        return 'border-emerald-200 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    }

    if (value === 'rejected') {
        return 'border-rose-200 bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300';
    }

    if (value === 'cancelled') {
        return 'border-wims-border bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400';
    }

    return 'border-amber-200 bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300';
};

const submitAbsenceRequest = () => {
    absenceForm.post('/wims/ketidakhadiran', {
        forceFormData: true,
        preserveScroll: true,
    });
};

const handleAbsenceProofChange = (event: Event) => {
    const input = event.target as HTMLInputElement | null;

    absenceForm.bukti = input?.files?.[0] ?? null;
};

const cancelAbsenceRequest = (id: number) => {
    absenceForm.delete(`/wims/ketidakhadiran/${id}`, {
        preserveScroll: true,
    });
};

watch(
    () => form.photo,
    (file) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }

        if (file instanceof File) {
            previewUrl.value = URL.createObjectURL(file);
        }
    },
);

const revokePreview = () => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const stopCamera = () => {
    cameraStream.value?.getTracks().forEach((track) => track.stop());
    cameraStream.value = null;

    if (videoPreview.value) {
        videoPreview.value.srcObject = null;
    }

    if (cameraState.value !== 'error') {
        cameraState.value = 'idle';
    }
};

const resetPhotoState = () => {
    cameraRequestId.value += 1;
    stopCamera();
    revokePreview();
    cameraError.value = '';
    cameraState.value = 'idle';
    cameraFrameReady.value = false;

    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const setPhotoFile = (file: File | null) => {
    if (file) {
        stopCamera();
        cameraError.value = '';
        cameraFrameReady.value = false;
        form.clearErrors('photo');
    }

    form.photo = file;
};

const openFilePicker = () => {
    cameraRequestId.value += 1;
    stopCamera();
    cameraError.value = '';
    cameraFrameReady.value = false;
    photoInput.value?.click();
};

const clearVerificationSession = () => {
    cameraRequestId.value += 1;
    stopCamera();
    cameraError.value = '';
    locationAccuracy.value = null;
    cameraFrameReady.value = false;
    revokePreview();
    form.photo = null;

    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const isMobileDevice = () =>
    /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);

const createCameraConstraints = (
    deviceId?: string,
): MediaStreamConstraints => ({
    video: deviceId
        ? {
              deviceId: {
                  exact: deviceId,
              },
              width: { ideal: 1280 },
              height: { ideal: 720 },
          }
        : isMobileDevice()
          ? {
                facingMode: {
                    ideal: 'user',
                },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            }
          : {
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
    audio: false,
});

const getPreferredCamera = (devices: MediaDeviceInfo[]) => {
    const physicalCamera = devices.find(
        (device) => !/virtual|obs|snap|manycam|droidcam/i.test(device.label),
    );

    return physicalCamera ?? devices[0] ?? null;
};

const getPreferredCameraId = async () => {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const cameras = devices.filter((device) => device.kind === 'videoinput');

    if (!cameras.length) {
        return '';
    }

    return getPreferredCamera(cameras)?.deviceId ?? cameras[0].deviceId;
};

const waitForVideoReady = async (video: HTMLVideoElement) => {
    if (
        video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA &&
        video.videoWidth > 0
    ) {
        return;
    }

    await new Promise<void>((resolve, reject) => {
        const timeout = window.setTimeout(() => {
            cleanup();
            reject(new Error('Video stream not ready'));
        }, 5000);

        const handleReady = () => {
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                cleanup();
                resolve();
            }
        };

        const cleanup = () => {
            window.clearTimeout(timeout);
            video.removeEventListener('loadedmetadata', handleReady);
            video.removeEventListener('canplay', handleReady);
        };

        video.addEventListener('loadedmetadata', handleReady);
        video.addEventListener('canplay', handleReady);
    });
};

const waitForRenderableFrame = async (video: HTMLVideoElement) => {
    const currentVideo: HTMLVideoElement = video;
    const frameCallbackVideo = currentVideo as HTMLVideoElement & {
        requestVideoFrameCallback?: (
            callback: VideoFrameRequestCallback,
        ) => number;
    };

    if (
        currentVideo.videoWidth > 0 &&
        currentVideo.videoHeight > 0 &&
        currentVideo.readyState >= HTMLMediaElement.HAVE_ENOUGH_DATA
    ) {
        cameraFrameReady.value = true;

        return;
    }

    await new Promise<void>((resolve, reject) => {
        const startedAt = Date.now();

        const finish = (ok: boolean) => {
            if (ok) {
                cameraFrameReady.value = true;
                resolve();
            } else {
                reject(new Error('Renderable frame timeout'));
            }
        };

        if (
            typeof frameCallbackVideo.requestVideoFrameCallback === 'function'
        ) {
            const checkFrame = () => {
                if (
                    currentVideo.videoWidth > 0 &&
                    currentVideo.videoHeight > 0 &&
                    currentVideo.readyState >=
                        HTMLMediaElement.HAVE_CURRENT_DATA
                ) {
                    finish(true);

                    return;
                }

                if (Date.now() - startedAt > 5000) {
                    finish(false);

                    return;
                }

                frameCallbackVideo.requestVideoFrameCallback?.(() => {
                    checkFrame();
                });
            };

            frameCallbackVideo.requestVideoFrameCallback?.(() => {
                checkFrame();
            });

            return;
        }

        const poll = () => {
            if (
                currentVideo.videoWidth > 0 &&
                currentVideo.videoHeight > 0 &&
                currentVideo.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA
            ) {
                finish(true);

                return;
            }

            if (Date.now() - startedAt > 5000) {
                finish(false);

                return;
            }

            window.setTimeout(poll, 120);
        };

        poll();
    });
};

const connectCameraStream = async (deviceId?: string) => {
    const stream = await navigator.mediaDevices.getUserMedia(
        createCameraConstraints(deviceId),
    );

    try {
        const video = videoPreview.value;

        if (!video) {
            throw new Error('Video preview element is not ready');
        }

        video.srcObject = stream;
        await video.play();
        await waitForVideoReady(video);
        await waitForRenderableFrame(video);

        return stream;
    } catch (error) {
        stream.getTracks().forEach((track) => track.stop());

        throw error;
    }
};

const createCanvasBlob = async (canvas: HTMLCanvasElement, quality: number) =>
    new Promise<Blob | null>((resolve) => {
        canvas.toBlob(resolve, 'image/jpeg', quality);
    });

const formatPhotoStampTime = (value: Date) =>
    new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(value);

const getPhotoStampLines = (capturedAt: Date) => {
    const lines = [`Waktu: ${formatPhotoStampTime(capturedAt)}`];

    if (form.latitude !== null && form.longitude !== null) {
        lines.push(`Koordinat: ${form.latitude}, ${form.longitude}`);
    }

    if (locationDistance.value !== null) {
        lines.push(
            `Jarak: ${Math.round(locationDistance.value)} m | Status: ${
                locationValidationState.value === 'inside'
                    ? 'Dalam area'
                    : 'Di luar area'
            }`,
        );
    }

    return lines;
};

const createStampedPhotoFile = async (
    source: CanvasImageSource,
    width: number,
    height: number,
    filename: string,
) => {
    const maxDimension = 1600;
    const scale = Math.min(1, maxDimension / Math.max(width, height));
    const outputWidth = Math.max(1, Math.round(width * scale));
    const outputHeight = Math.max(1, Math.round(height * scale));

    const canvas = document.createElement('canvas');
    canvas.width = outputWidth;
    canvas.height = outputHeight;

    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Gagal memproses hasil kamera.');
    }

    context.drawImage(source, 0, 0, outputWidth, outputHeight);

    const capturedAt = new Date(currentTime.value.getTime());
    const stampLines = getPhotoStampLines(capturedAt);
    const fontSize = Math.max(18, Math.round(outputWidth * 0.024));
    const lineHeight = Math.round(fontSize * 1.45);
    const paddingX = Math.round(fontSize * 0.9);
    const paddingY = Math.round(fontSize * 0.7);
    const blockHeight = paddingY * 2 + lineHeight * stampLines.length;

    context.fillStyle = 'rgba(15, 23, 42, 0.78)';
    context.fillRect(0, outputHeight - blockHeight, outputWidth, blockHeight);

    context.font = `600 ${fontSize}px Arial`;
    context.textBaseline = 'top';
    context.fillStyle = '#FFFFFF';

    stampLines.forEach((line, index) => {
        context.fillText(
            line,
            paddingX,
            outputHeight - blockHeight + paddingY + index * lineHeight,
        );
    });

    const qualities = [0.92, 0.86, 0.8, 0.74];
    let blob: Blob | null = null;

    for (const quality of qualities) {
        blob = await createCanvasBlob(canvas, quality);

        if (blob && blob.size <= maxPhotoSizeBytes) {
            break;
        }
    }

    if (!blob) {
        throw new Error('Gagal menyiapkan foto verifikasi.');
    }

    if (blob.size > maxPhotoSizeBytes) {
        throw new Error(`Ukuran foto melebihi batas ${maxPhotoSizeLabel}.`);
    }

    return new File([blob], filename, {
        type: 'image/jpeg',
    });
};

const createImageElement = (file: File) =>
    new Promise<HTMLImageElement>((resolve, reject) => {
        const objectUrl = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(image);
        };

        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('File foto tidak dapat dibaca.'));
        };

        image.src = objectUrl;
    });

const openCamera = async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
        cameraState.value = 'error';
        cameraError.value =
            'Browser ini tidak mendukung kamera langsung. Gunakan pilih file sebagai cadangan.';

        return false;
    }

    const requestId = ++cameraRequestId.value;

    stopCamera();
    cameraError.value = '';
    cameraState.value = 'loading';
    cameraFrameReady.value = false;

    try {
        await nextTick();

        let stream = await connectCameraStream();
        const preferredCameraId = await getPreferredCameraId();
        const activeDeviceId = stream
            .getVideoTracks()[0]
            ?.getSettings().deviceId;

        if (preferredCameraId && activeDeviceId !== preferredCameraId) {
            stream.getTracks().forEach((track) => track.stop());
            stream = await connectCameraStream(preferredCameraId);
        }

        if (requestId !== cameraRequestId.value) {
            stream.getTracks().forEach((track) => track.stop());

            return false;
        }

        cameraStream.value = stream;

        cameraState.value = 'ready';

        return true;
    } catch {
        if (requestId !== cameraRequestId.value) {
            return false;
        }

        cameraState.value = 'error';
        cameraError.value =
            'Kamera belum mengirim frame video ke halaman. Coba buka kamera lagi; sistem akan reconnect otomatis ke device yang tersedia.';

        return false;
    }
};

const capturePhoto = async () => {
    const video = videoPreview.value;

    if (
        !video ||
        cameraState.value !== 'ready' ||
        !cameraFrameReady.value ||
        locationValidationState.value !== 'inside'
    ) {
        return;
    }

    if (video.videoWidth === 0 || video.videoHeight === 0) {
        cameraState.value = 'error';
        cameraError.value =
            'Kamera sudah diizinkan, tetapi frame video belum siap. Buka kamera lagi setelah preview tampil normal.';

        return;
    }

    try {
        const file = await createStampedPhotoFile(
            video,
            video.videoWidth,
            video.videoHeight,
            `absensi-${Date.now()}.jpg`,
        );

        setPhotoFile(file);
        stopCamera();
    } catch (error) {
        cameraState.value = 'error';
        cameraError.value =
            error instanceof Error
                ? error.message
                : 'Gagal memproses hasil kamera. Coba lagi.';
    }
};

const handlePhotoChange = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        setPhotoFile(null);

        return;
    }

    try {
        const image = await createImageElement(file);
        const stampedFile = await createStampedPhotoFile(
            image,
            image.naturalWidth,
            image.naturalHeight,
            `absensi-${Date.now()}.jpg`,
        );

        setPhotoFile(stampedFile);
    } catch (error) {
        form.setError(
            'photo',
            error instanceof Error
                ? error.message
                : 'Foto tidak bisa diproses. Coba pilih file lain.',
        );

        if (photoInput.value) {
            photoInput.value.value = '';
        }
    }
};

const getLocation = () =>
    new Promise<boolean>((resolve) => {
        if (!navigator.geolocation) {
            locationState.value = 'error';
            locationError.value =
                'Browser ini tidak mendukung pengambilan lokasi.';

            resolve(false);

            return;
        }

        locationState.value = 'loading';
        locationError.value = '';
        locationAccuracy.value = null;

        const requestPosition = () =>
            new Promise<GeolocationPosition>(
                (positionResolve, positionReject) => {
                    navigator.geolocation.getCurrentPosition(
                        positionResolve,
                        positionReject,
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0,
                        },
                    );
                },
            );

        const setLocationError = (message: string, resetAccuracy = true) => {
            form.latitude = null;
            form.longitude = null;

            if (resetAccuracy) {
                locationAccuracy.value = null;
            }

            locationState.value = 'error';
            locationError.value = message;
            resolve(false);
        };

        const attempts = Array.from({ length: 3 }, () => requestPosition());

        Promise.allSettled(attempts)
            .then((results) => {
                const fulfilled = results
                    .filter(
                        (
                            result,
                        ): result is PromiseFulfilledResult<GeolocationPosition> =>
                            result.status === 'fulfilled',
                    )
                    .map((result) => result.value);

                if (!fulfilled.length) {
                    const firstRejected = results.find(
                        (result): result is PromiseRejectedResult =>
                            result.status === 'rejected',
                    );
                    const reason = firstRejected?.reason as
                        | GeolocationPositionError
                        | undefined;
                    const reasonCode = reason?.code;

                    if (reasonCode === 1) {
                        setLocationError(
                            'Izin lokasi ditolak. Aktifkan lokasi lalu coba lagi.',
                        );

                        return;
                    }

                    if (reasonCode === 2) {
                        setLocationError(
                            'Lokasi tidak tersedia. Pastikan GPS aktif.',
                        );

                        return;
                    }

                    if (reasonCode === 3) {
                        setLocationError(
                            'Pengambilan lokasi terlalu lama. Coba sekali lagi.',
                        );

                        return;
                    }

                    setLocationError(
                        'Terjadi kendala saat mengambil lokasi Anda.',
                    );

                    return;
                }

                const bestPosition = fulfilled.reduce((best, current) =>
                    current.coords.accuracy < best.coords.accuracy
                        ? current
                        : best,
                );

                locationAccuracy.value = bestPosition.coords.accuracy;

                if (
                    bestPosition.coords.accuracy >
                    locationAccuracyThreshold.value
                ) {
                    setLocationError(
                        `Akurasi GPS perangkat masih terlalu lemah (${Math.round(bestPosition.coords.accuracy)} m). Coba pindah ke area terbuka atau gunakan HP agar lokasi tidak meleset jauh.`,
                        false,
                    );

                    return;
                }

                form.latitude = Number(bestPosition.coords.latitude.toFixed(6));
                form.longitude = Number(
                    bestPosition.coords.longitude.toFixed(6),
                );
                locationState.value = 'success';
                resolve(true);
            })
            .catch(() => {
                setLocationError('Terjadi kendala saat mengambil lokasi Anda.');
            });
    });

const startVerification = async () => {
    if (isVerificationLoading.value || form.processing) {
        return;
    }

    clearVerificationSession();
    form.clearErrors('photo');

    await Promise.all([getLocation(), openCamera()]);
};

const submit = () => {
    form.post(absensiRoutes.store.url(), {
        forceFormData: true,
        preserveScroll: true,
    });
};

onMounted(() => {
    timeInterval.value = window.setInterval(() => {
        currentTime.value = new Date(currentTime.value.getTime() + 1000);
    }, 1000);
});

onBeforeUnmount(() => {
    if (timeInterval.value) {
        window.clearInterval(timeInterval.value);
    }

    stopCamera();
    revokePreview();
});
</script>

<template>
    <Head title="Presensi Harian" />

    <div class="min-h-full">
        <!-- Animated background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-blue-400/[0.06] to-cyan-400/[0.04] blur-3xl animate-pulse dark:from-blue-500/[0.04] dark:to-cyan-400/[0.025]" style="animation-duration: 7s;" />
            <div class="absolute top-1/3 -left-16 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-indigo-400/[0.05] to-violet-400/[0.03] blur-3xl animate-pulse dark:from-blue-600/[0.03] dark:to-indigo-500/[0.02]" style="animation-duration: 10s; animation-delay: 3s;" />
            <div class="absolute -bottom-16 right-1/3 h-[350px] w-[350px] rounded-full bg-gradient-to-tl from-cyan-400/[0.04] to-blue-400/[0.03] blur-3xl animate-pulse dark:from-cyan-400/[0.025] dark:to-blue-500/[0.02]" style="animation-duration: 12s; animation-delay: 5s;" />
            <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.04]" style="background-image: radial-gradient(circle, currentColor 0.5px, transparent 0.5px); background-size: 24px 24px;" />
        </div>

        <div class="relative space-y-4 lg:space-y-5">
            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-2xl lg:rounded-3xl">
                <!-- Hero gradient background -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 dark:from-[#1D3E8A] dark:via-[#1F5FCC] dark:to-[#0E7ACF]" />
                <div class="absolute inset-0 bg-gradient-to-t from-blue-700/30 via-transparent to-transparent dark:from-[#07152f]/55 dark:via-[#0f172a]/10 dark:to-transparent" />
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.18),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.18),_transparent_32%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.10),_transparent_28%)]" />
                <!-- Decorative shapes -->
                <div class="absolute top-0 right-0 h-72 w-72 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/[0.08] blur-3xl dark:bg-white/[0.04]" />
                <div class="absolute bottom-0 left-0 h-56 w-56 -translate-x-1/4 translate-y-1/3 rounded-full bg-blue-900/20 blur-3xl dark:bg-slate-950/35" />
                <div class="absolute top-6 right-8 hidden h-16 w-16 rounded-full border border-white/[0.08] dark:border-white/[0.05] lg:block" />
                <div class="absolute top-12 right-16 hidden h-28 w-28 rounded-full border border-white/[0.05] dark:border-white/[0.03] lg:block" />
                <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.03]" style="background-image: radial-gradient(circle, white 0.5px, transparent 0.5px); background-size: 20px 20px;" />

                <div class="relative px-5 py-6 sm:px-7 sm:py-7 lg:px-8 lg:py-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <h1 class="text-[20px] font-bold tracking-tight text-white sm:text-[24px] lg:text-[30px] leading-[1.15]">
                                Presensi Harian
                            </h1>
                            <p class="mt-2 text-[13px] leading-relaxed text-white/78 dark:text-white/70 sm:text-sm">
                                Catat kehadiran magang hari ini dengan cepat dan akurat.
                            </p>
                        </div>

                        <div class="lg:min-w-[180px]">
                            <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur-md dark:bg-white/[0.07] dark:ring-white/10">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Waktu saat ini</p>
                                <p class="mt-1 text-[20px] font-bold tracking-tight text-white sm:text-[22px]">
                                    {{ formattedTime }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-white/60">{{ formattedDate }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="flash.success">
                <Alert class="rounded-xl border-emerald-200/60 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-300">
                    <CheckCircle2 class="size-4" />
                    <AlertTitle class="text-sm font-semibold">Check-in berhasil</AlertTitle>
                    <AlertDescription class="text-xs">{{ flash.success }}</AlertDescription>
                </Alert>
            </div>

            <div v-if="globalError">
                <Alert class="rounded-xl border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 text-rose-800 dark:text-rose-300">
                    <AlertCircle class="size-4" />
                    <AlertTitle class="text-sm font-semibold">Check-in gagal</AlertTitle>
                    <AlertDescription class="text-xs">{{ globalError }}</AlertDescription>
                </Alert>
            </div>

            <div v-if="flash.distance !== undefined || flash.is_valid !== undefined">
                <Alert
                    :class="
                        flash.is_valid === false || flash.is_valid === 'false'
                            ? 'rounded-xl border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 text-rose-800 dark:text-rose-300'
                            : 'rounded-xl border-blue-200/60 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/10 text-blue-800 dark:text-blue-300'
                    "
                >
                    <Navigation class="size-4" />
                    <AlertTitle class="text-sm font-semibold">Validasi lokasi</AlertTitle>
                    <AlertDescription class="text-xs space-y-1">
                        <p v-if="flash.distance !== undefined">
                            Jarak dari titik absensi: <span class="font-semibold">{{ flash.distance }}</span>
                        </p>
                        <p v-if="flash.is_valid !== undefined">
                            Status radius: <span class="font-semibold">{{ flash.is_valid === true || flash.is_valid === 'true' ? 'Valid' : 'Di luar area' }}</span>
                        </p>
                    </AlertDescription>
                </Alert>
            </div>

            <!-- Main Content Grid -->
            <div class="grid items-start gap-4 xl:grid-cols-[1.5fr_1fr] xl:gap-5">
                <!-- Left Column: Presensi Card -->
                <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                    <!-- Card Header -->
                    <div class="border-b border-wims-border/50 px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                    <Clock3 class="size-5" />
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-wims-text">Presensi Hari Ini</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Verifikasi lokasi & foto, lalu check-in/out</p>
                                </div>
                            </div>
                            <Badge class="w-fit rounded-full border px-2.5 py-0.5 text-[10px] font-bold" :class="statusClasses">
                                {{ statusLabel }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="space-y-4 px-5 py-5 sm:px-6">
                        <!-- Verification summary -->
                        <div class="rounded-xl border px-4 py-3 text-[12px] leading-relaxed" :class="verificationSummaryClass">
                            {{ verificationSummaryMessage }}
                        </div>

                        <!-- Camera / Photo preview area -->
                        <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 p-3">
                            <input ref="photoInput" type="file" accept="image/*" capture="user" class="hidden" @change="handlePhotoChange" />

                            <div v-if="previewUrl" class="overflow-hidden rounded-lg border border-wims-border/60 bg-slate-100 dark:bg-slate-700/50">
                                <img :src="previewUrl" alt="Preview foto absensi" class="max-h-[200px] w-full object-cover" />
                            </div>
                            <div v-else-if="cameraState === 'loading' || cameraState === 'ready'" class="space-y-2.5">
                                <div class="overflow-hidden rounded-lg border border-wims-border/60 bg-slate-950 shadow-inner">
                                    <video ref="videoPreview" autoplay muted playsinline class="max-h-[200px] w-full object-cover" />
                                </div>
                                <p v-if="locationValidationState === 'outside'" class="rounded-lg border border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 px-3 py-2 text-[10px] leading-relaxed text-rose-700 dark:text-rose-300">
                                    Preview kamera aktif, foto hanya bisa diambil saat posisi masuk radius presensi.
                                </p>
                            </div>
                            <div v-else-if="cameraState === 'error'" class="flex min-h-[100px] flex-col items-center justify-center gap-2 px-3 text-center">
                                <AlertCircle class="size-6 text-rose-500 dark:text-rose-400" />
                                <div>
                                    <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Kamera belum bisa digunakan</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">{{ cameraError }}</p>
                                </div>
                            </div>
                            <div v-else class="flex min-h-[80px] items-center gap-3 px-1">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-wims-card ring-1 ring-wims-border/60 dark:ring-slate-600">
                                    <Camera class="size-4.5 text-slate-400 dark:text-slate-500" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">Belum ada foto verifikasi</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">Foto muncul setelah lokasi & kamera siap.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="h-10 rounded-xl border-wims-border/60 bg-wims-card text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-blue-300/60 hover:bg-blue-50 dark:hover:bg-blue-500/10 disabled:opacity-50"
                                :disabled="isVerificationLoading"
                                @click="startVerification"
                            >
                                <LoaderCircle v-if="isVerificationLoading" class="size-3.5 animate-spin" />
                                <LocateFixed v-else class="size-3.5" />
                                {{ verificationButtonText }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-10 rounded-xl border-wims-border/60 bg-wims-card text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-blue-300/60 hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                @click="openFilePicker"
                            >
                                <ImagePlus class="size-3.5" />
                                Ambil Foto / Pilih File
                            </Button>
                        </div>

                        <!-- Capture button (when camera ready & inside) -->
                        <Button
                            v-if="canCapturePhoto && !previewUrl"
                            type="button"
                            class="h-10 w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 active:scale-[0.98] transition-all"
                            @click="capturePhoto"
                        >
                            <Camera class="size-4" />
                            Ambil Foto Sekarang
                        </Button>

                        <!-- Check-in / Check-out -->
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <Button
                                type="button"
                                class="h-11 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-[13px] font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#1F58C7] dark:to-[#1184D8] dark:shadow-[0_16px_36px_-18px_rgba(8,15,30,0.88)] dark:hover:shadow-[0_18px_40px_-18px_rgba(8,15,30,0.94)] disabled:opacity-50 disabled:shadow-none disabled:from-slate-300 disabled:to-slate-300 disabled:text-slate-500 dark:disabled:from-slate-700 dark:disabled:to-slate-700 dark:disabled:text-slate-400"
                                :disabled="isCheckInDisabled"
                                @click="submit"
                            >
                                <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                                <CheckCircle2 v-else class="size-4" />
                                {{ checkInButtonText }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="h-11 w-full rounded-xl border-wims-border/60 bg-wims-card text-[13px] font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/30 disabled:opacity-50"
                                :disabled="isCheckoutDisabled"
                                @click="checkout"
                            >
                                <LogOut class="size-4" />
                                {{ checkoutButtonText }}
                            </Button>
                        </div>

                        <!-- Status info grid -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</p>
                                <Badge class="mt-1.5 rounded-full border px-2 py-0.5 text-[10px] font-bold" :class="statusClasses">{{ statusLabel }}</Badge>
                            </div>
                            <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jam masuk</p>
                                <p class="mt-1.5 text-[13px] font-bold text-wims-text">{{ formatAttendanceTime(checkedInAt) }}</p>
                            </div>
                            <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jam keluar</p>
                                <p class="mt-1.5 text-[13px] font-bold text-wims-text">{{ formatAttendanceTime(checkedOutAt) }}</p>
                            </div>
                            <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lokasi</p>
                                <Badge variant="outline" class="mt-1.5 max-w-full rounded-full px-2 py-0.5 text-[10px] font-bold" :class="locationBadgeClasses">{{ locationBadgeLabel }}</Badge>
                            </div>
                        </div>

                        <div
                            v-if="attendance.check_in_photo_url || attendance.check_out_photo_url"
                            class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Bukti Hari Ini
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <a
                                    v-if="attendance.check_in_photo_url"
                                    :href="attendance.check_in_photo_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-semibold text-blue-700 ring-1 ring-blue-200/60 transition-colors hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30 dark:hover:bg-blue-500/20"
                                >
                                    Lihat foto masuk
                                </a>
                                <a
                                    v-if="attendance.check_out_photo_url"
                                    :href="attendance.check_out_photo_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-semibold text-blue-700 ring-1 ring-blue-200/60 transition-colors hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30 dark:hover:bg-blue-500/20"
                                >
                                    Lihat foto pulang
                                </a>
                            </div>
                        </div>

                        <!-- Hint -->
                        <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-2.5 text-[11px] leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ actionHint }}
                        </div>

                        <!-- Technical details -->
                        <details class="rounded-xl border border-wims-border/60 bg-wims-card/80 backdrop-blur-sm px-4 py-3">
                            <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">Detail teknis</summary>
                            <div class="mt-3 space-y-2.5">
                                <div class="grid gap-2.5 sm:grid-cols-3">
                                    <div class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jarak</p>
                                        <p class="mt-1 text-xs font-bold" :class="isOutsideAttendanceArea ? 'text-rose-600 dark:text-rose-400' : locationValidationState === 'inside' ? 'text-emerald-600 dark:text-emerald-400' : 'text-wims-text'">{{ locationDistanceLabel }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Akurasi GPS</p>
                                        <p class="mt-1 text-xs font-bold text-wims-text">{{ locationAccuracyLabel }}</p>
                                    </div>
                                    <div class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Radius</p>
                                        <p class="mt-1 text-xs font-bold text-wims-text">{{ attendance.radius ? `${attendance.radius}m` : '-' }}</p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                    <p class="text-[11px] font-bold text-wims-text">Ringkasan verifikasi</p>
                                    <p class="mt-1 text-[10px] leading-relaxed text-slate-600 dark:text-slate-400">Lokasi: {{ locationStatusLabel }}. Foto: {{ isPhotoReady ? 'sudah siap dikirim.' : 'belum tersedia.' }}</p>
                                    <p class="mt-1 text-[10px] leading-relaxed text-slate-600 dark:text-slate-400">{{ isAttendanceReady ? 'Presensi siap dilakukan.' : 'Presensi belum bisa dilakukan sampai lokasi dan foto siap.' }}</p>
                                </div>
                            </div>
                        </details>

                        <!-- Form errors -->
                        <p v-if="form.errors.latitude || form.errors.longitude || form.errors.pendaftaran_id" class="rounded-xl border border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 px-3 py-2 text-xs text-rose-700 dark:text-rose-300">
                            {{ form.errors.latitude || form.errors.longitude || form.errors.pendaftaran_id }}
                        </p>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <aside class="space-y-4">
                    <!-- Pengajuan Ketidakhadiran Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <Sparkles class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-[15px] font-bold text-wims-text">Ketidakhadiran</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ajukan izin atau sakit</p>
                                    </div>
                                </div>
                                <Badge variant="outline" class="shrink-0 rounded-full border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                    {{ absenceRequests.length }} riwayat
                                </Badge>
                            </div>

                            <div v-if="todayAbsence" class="mt-4 rounded-xl border border-amber-200/60 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10 px-4 py-3 text-xs text-amber-800 dark:text-amber-300">
                                <p class="font-bold">Hari ini tercatat {{ absenceKindLabel(todayAbsence.jenis) }}.</p>
                                <p class="mt-1 leading-relaxed text-amber-700 dark:text-amber-400">{{ todayAbsence.alasan || 'Tidak ada alasan tambahan.' }}</p>
                            </div>

                            <Button type="button" class="mt-4 h-10 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-xs font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]" @click="absencePanelOpen = true">
                                Ajukan Izin / Sakit
                            </Button>
                        </div>
                    </div>

                    <!-- Informasi Lokasi Card -->
                    <div class="rounded-2xl bg-wims-card/90 backdrop-blur-sm border border-wims-border/50 shadow-[0_1px_3px_rgba(0,0,0,0.04)] transition-all duration-300 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.06)]">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                    <Building2 class="size-5" />
                                </div>
                                <div>
                                    <p class="text-[15px] font-bold text-wims-text">Informasi Lokasi</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Data perusahaan & radius</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2.5">
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Perusahaan</p>
                                    <p class="mt-1.5 text-[13px] font-bold text-wims-text leading-tight">{{ attendance.company_name || 'Belum ada perusahaan' }}</p>
                                </div>
                                <div class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Radius presensi</p>
                                    <p class="mt-1.5 text-[13px] font-bold text-wims-text">{{ attendance.radius ? `${attendance.radius}m` : '-' }}</p>
                                </div>

                                <details class="rounded-xl border border-wims-border/60 bg-wims-card/80 backdrop-blur-sm px-4 py-3">
                                    <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">Detail teknis lokasi</summary>
                                    <div class="mt-2.5">
                                        <div class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-2.5">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ambang akurasi GPS</p>
                                            <p class="mt-1 text-xs font-bold text-wims-text">{{ Math.round(locationAccuracyThreshold) }} m</p>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Absence Request Modal -->
            <div v-if="absencePanelOpen" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/40 backdrop-blur-sm p-4 sm:items-center sm:p-6" @click.self="absencePanelOpen = false">
                <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-wims-border/50 bg-wims-card shadow-2xl">
                    <!-- Modal Header -->
                    <div class="border-b border-wims-border/50 px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[15px] font-bold text-wims-text">Pengajuan Ketidakhadiran</p>
                                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Ajukan izin atau sakit tanpa meninggalkan halaman.</p>
                            </div>
                            <button type="button" class="rounded-lg border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-colors" @click="absencePanelOpen = false">
                                Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="overflow-y-auto px-5 py-5 sm:px-6">
                        <div class="space-y-4">
                            <div v-if="todayAbsence" class="rounded-xl border border-amber-200/60 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10 px-4 py-3 text-xs text-amber-800 dark:text-amber-300">
                                <p class="font-bold">Hari ini tercatat {{ absenceKindLabel(todayAbsence.jenis) }} yang telah disetujui.</p>
                                <p class="mt-1 leading-relaxed text-amber-700 dark:text-amber-400">{{ todayAbsence.alasan || 'Tidak ada alasan tambahan.' }}</p>
                            </div>

                            <div class="grid gap-5 lg:grid-cols-[1fr_minmax(280px,0.9fr)]">
                                <!-- Form -->
                                <div class="space-y-3">
                                    <div class="grid gap-2.5 sm:grid-cols-2">
                                        <label class="block space-y-1.5">
                                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tanggal Mulai</span>
                                            <input v-model="absenceForm.tanggal_mulai" type="date" class="h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card px-3 text-xs text-wims-text transition-colors outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10" />
                                        </label>
                                        <label class="block space-y-1.5">
                                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tanggal Selesai</span>
                                            <input v-model="absenceForm.tanggal_selesai" type="date" class="h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card px-3 text-xs text-wims-text transition-colors outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10" />
                                        </label>
                                    </div>

                                    <label class="block space-y-1.5">
                                        <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Jenis Ketidakhadiran</span>
                                        <select v-model="absenceForm.jenis" class="h-10 w-full rounded-xl border border-wims-border/60 bg-wims-card px-3 text-xs text-wims-text transition-colors outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10">
                                            <option value="izin">Izin</option>
                                            <option value="sakit">Sakit</option>
                                        </select>
                                    </label>

                                    <label class="block space-y-1.5">
                                        <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Alasan</span>
                                        <textarea v-model="absenceForm.alasan" rows="3" class="min-h-20 w-full rounded-xl border border-wims-border/60 bg-wims-card px-3 py-2.5 text-xs leading-relaxed text-wims-text transition-colors outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 dark:focus:ring-blue-400/10" placeholder="Jelaskan alasan Anda tidak berangkat PKL." />
                                    </label>

                                    <label class="block space-y-1.5">
                                        <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Bukti Pendukung</span>
                                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-wims-border/60 bg-wims-card px-3 py-2 text-xs text-slate-700 dark:text-slate-300 file:mr-3 file:rounded-full file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 dark:file:bg-blue-500/15 dark:file:text-blue-400" @change="handleAbsenceProofChange" />
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500">Opsional. Format: JPG, PNG, atau PDF maks 5 MB.</p>
                                    </label>

                                    <div v-if="absenceForm.errors.tanggal_mulai || absenceForm.errors.tanggal_selesai || absenceForm.errors.jenis || absenceForm.errors.alasan || absenceForm.errors.bukti" class="rounded-xl border border-rose-200/60 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-500/10 px-3 py-2 text-xs text-rose-700 dark:text-rose-300">
                                        {{ absenceForm.errors.tanggal_mulai || absenceForm.errors.tanggal_selesai || absenceForm.errors.jenis || absenceForm.errors.alasan || absenceForm.errors.bukti }}
                                    </div>

                                    <div class="flex flex-col gap-2.5 sm:flex-row">
                                        <Button type="button" class="h-10 flex-1 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-xs font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)] disabled:opacity-50 disabled:shadow-none" :disabled="absenceForm.processing || !attendance.pendaftaran_id" @click="submitAbsenceRequest">
                                            <LoaderCircle v-if="absenceForm.processing" class="size-3.5 animate-spin" />
                                            <span v-else>Kirim Pengajuan</span>
                                        </Button>
                                        <Button type="button" variant="outline" class="h-10 rounded-xl border-wims-border/60 bg-wims-card text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/30" @click="absencePanelOpen = false">
                                            Batal
                                        </Button>
                                    </div>
                                </div>

                                <!-- History -->
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[13px] font-bold text-wims-text">Riwayat Pengajuan</p>
                                        <Badge variant="outline" class="rounded-full border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                            {{ absenceRequests.length }} item
                                        </Badge>
                                    </div>

                                    <div v-for="item in absenceRequests" :key="item.id" class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-wims-text">{{ absenceKindLabel(item.jenis) }} - {{ item.tanggal_label || '-' }}</p>
                                                <p class="mt-1 text-[10px] leading-relaxed text-slate-500 dark:text-slate-400">{{ item.alasan || '-' }}</p>
                                            </div>
                                            <Badge variant="outline" class="flex-shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold" :class="absenceStatusClass(item.status)">
                                                {{ absenceStatusLabel(item.status) }}
                                            </Badge>
                                        </div>

                                        <p v-if="item.catatan_mitra" class="mt-2.5 rounded-lg border border-wims-border/60 bg-wims-card/80 px-3 py-2 text-[10px] leading-relaxed text-slate-600 dark:text-slate-400">
                                            Catatan mitra: {{ item.catatan_mitra }}
                                        </p>

                                        <div class="mt-2.5 flex flex-wrap gap-2">
                                            <a v-if="item.bukti_url" :href="item.bukti_url" target="_blank" rel="noreferrer" class="rounded-full bg-blue-50 dark:bg-blue-500/10 px-3 py-1 text-[10px] font-semibold text-blue-700 dark:text-blue-400 ring-1 ring-blue-200/60 dark:ring-blue-500/30 transition-colors hover:bg-blue-100 dark:hover:bg-blue-500/20">
                                                Lihat Bukti
                                            </a>
                                            <button v-if="item.can_cancel" type="button" class="rounded-full bg-rose-50 dark:bg-rose-500/10 px-3 py-1 text-[10px] font-semibold text-rose-700 dark:text-rose-300 ring-1 ring-rose-200/60 dark:ring-rose-500/30 transition-colors hover:bg-rose-100 dark:hover:bg-rose-500/20" @click="cancelAbsenceRequest(item.id)">
                                                Batalkan
                                            </button>
                                        </div>
                                    </div>

                                    <p v-if="!absenceRequests.length" class="rounded-xl border border-wims-border/60 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        Belum ada pengajuan ketidakhadiran pada periode PKL aktif.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


