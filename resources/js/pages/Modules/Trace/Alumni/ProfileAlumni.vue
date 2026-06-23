<script setup lang="ts">
import { ref, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import TraceAlumniLayout from "@/layouts/TraceAlumniLayout.vue";
import type { BreadcrumbItem } from "@/types";
import type { CareerHistory, EducationHistory } from "@/types/trace";
import ProfileView from "./components/ProfileView.vue";
import ProfileEditModal from "./components/ProfileEditModal.vue";
import { CheckCircle2, UserCircle } from "lucide-vue-next";
import { TPageHeader } from "@/components/Trace";
import { update } from "@/routes/module/trace/profile-alumni/index";

const props = defineProps<{
    roleName: string;
    alumni: {
        user_id: number;
        name: string;
        email: string;
        nomor_induk: string;
        no_telepon: string | null;
        foto_path: string | null;
        banner_path: string | null;
        tahun_lulus: number | null;
        tanggal_lahir: string | null;
        program_studi_id: number | null;

        bio: string | null;
        location: string | null;
        website: string | null;
        linkedin: string | null;
        github: string | null;
        instagram: string | null;
        twitter: string | null;

        profil_id: number;
        angkatan: number | null;
        alamat_rumah: string | null;
        latitude_rumah: number | null;
        longitude_rumah: number | null;
        jenis_kelamin: "L" | "P" | null;
        nik: string | null;
        npwp: string | null;
        provinsi_id: number | null;
        kota_id: number | null;
        completeness_percentage: number;

        careers: CareerHistory[];
        education_histories: EducationHistory[];
    };
    provinsis: { id: number; name: string }[];
    kotas: { id: number; name: string; provinsi_id: number }[];
    programStudis: { id: number; nama: string; kode: string }[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: "Dashboard", href: "/trace" },
    { title: "Profil Karir", href: "/trace/profile-alumni" },
];

const isEditOpen = ref(false);
const showSuccessAlert = ref(false);

const form = useForm({
    name: props.alumni.name || "",
    nomor_induk: props.alumni.nomor_induk || "",
    tahun_lulus: props.alumni.tahun_lulus || null,
    no_telepon: props.alumni.no_telepon || "",
    program_studi_id: props.alumni.program_studi_id || "",
    bio: props.alumni.bio || "",
    location: props.alumni.location || "",
    website: props.alumni.website || "",
    github: props.alumni.github || "",
    instagram: props.alumni.instagram || "",
    twitter: props.alumni.twitter || "",
    linkedin: props.alumni.linkedin || "",

    jenis_kelamin: props.alumni.jenis_kelamin || "",
    angkatan: props.alumni.angkatan || "",
    nik: props.alumni.nik || "",
    npwp: props.alumni.npwp || "",
    provinsi_id: props.alumni.provinsi_id || null,
    kota_id: props.alumni.kota_id || null,
    alamat_rumah: props.alumni.alamat_rumah || "",
    latitude_rumah: props.alumni.latitude_rumah,
    longitude_rumah: props.alumni.longitude_rumah,
});

watch(
    () => props.alumni,
    (newVal) => {
        form.name = newVal.name || "";
        form.nomor_induk = newVal.nomor_induk || "";
        form.tahun_lulus = newVal.tahun_lulus || null;
        form.no_telepon = newVal.no_telepon || "";
        form.program_studi_id = newVal.program_studi_id || "";
        form.bio = newVal.bio || "";
        form.location = newVal.location || "";
        form.website = newVal.website || "";
        form.github = newVal.github || "";
        form.instagram = newVal.instagram || "";
        form.twitter = newVal.twitter || "";
        form.linkedin = newVal.linkedin || "";

        form.jenis_kelamin = newVal.jenis_kelamin || "";
        form.angkatan = newVal.angkatan || "";
        form.nik = newVal.nik || "";
        form.npwp = newVal.npwp || "";
        form.provinsi_id = newVal.provinsi_id || null;
        form.kota_id = newVal.kota_id || null;
        form.alamat_rumah = newVal.alamat_rumah || "";
        form.latitude_rumah = newVal.latitude_rumah;
        form.longitude_rumah = newVal.longitude_rumah;
    },
    { deep: true },
);

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            isEditOpen.value = false;
            showSuccessAlert.value = true;
            setTimeout(() => {
                showSuccessAlert.value = false;
            }, 4000);
        },
        onError: () => {
            toast.error("Gagal menyimpan profil. Periksa kembali data Anda.");
        },
    });
};
</script>

<template>
    <TraceAlumniLayout
        title="Profil Karir"
        :breadcrumbs="breadcrumbItems"
        :role-name="roleName"
    >
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Page Header -->
            <TPageHeader
                title="Profil Alumni"
                description="Perbarui data diri, domisili, dan kontak Anda."
                :icon="UserCircle"
            />

            <Transition name="slide">
                <div
                    v-if="showSuccessAlert"
                    class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 rounded-2xl p-4 flex items-start gap-3 shadow-xs"
                >
                    <CheckCircle2
                        class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"
                    />
                    <div>
                        <p
                            class="text-sm font-bold text-emerald-800 dark:text-emerald-250"
                        >
                            Pembaruan Berhasil
                        </p>
                        <p
                            class="text-xs font-semibold text-emerald-600 dark:text-emerald-400/90 mt-0.5"
                        >
                            Profil karir dan data domisili tracer study Anda
                            telah berhasil disimpan.
                        </p>
                    </div>
                </div>
            </Transition>

            <!-- Profile Read-Only View -->
            <ProfileView
                :alumni="alumni"
                :programStudis="programStudis"
                :provinsis="provinsis"
                :kotas="kotas"
                @edit="isEditOpen = true"
            />

            <!-- Profile Edit Modal -->
            <ProfileEditModal
                :isOpen="isEditOpen"
                :form="form"
                :provinsis="provinsis"
                :kotas="kotas"
                :programStudis="programStudis"
                @close="isEditOpen = false"
                @submit="submit"
            />
        </div>
    </TraceAlumniLayout>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease-out;
}
.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
</style>
