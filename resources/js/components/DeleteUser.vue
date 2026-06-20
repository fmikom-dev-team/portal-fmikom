<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, useTemplateRef } from "vue";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Button } from "@/components/ui/button";
import {
	Dialog,
	DialogClose,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { AlertTriangle, Clock, Ban, Loader2 } from "lucide-vue-next";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const passwordInput = useTemplateRef("passwordInput");
const isOpen = ref(false);

const requestForm = useForm({
	password: "",
});

const cancelForm = useForm({});

const submitRequest = () => {
	requestForm.post("/settings/profile/deletion-request", {
		preserveScroll: true,
		onSuccess: () => {
			requestForm.reset();
			isOpen.value = false;
		},
		onError: () => {
			passwordInput.value?.focus();
		},
	});
};

const cancelRequest = () => {
	if (confirm("Apakah Anda yakin ingin membatalkan pengajuan penghapusan akun?")) {
		cancelForm.post("/settings/profile/deletion-request/cancel", {
			preserveScroll: true,
		});
	}
};

const formatDate = (dateString: string | null) => {
	if (!dateString) return "";
	return new Date(dateString).toLocaleDateString("id-ID", {
		day: "numeric",
		month: "long",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
};
</script>

<template>
    <div v-if="user?.user_type !== 'super_admin'" class="space-y-6">
        <!-- Case 1: Deletion request is pending approval -->
        <div v-if="user?.deletion_requested_at" class="space-y-4">
            <Heading
                variant="small"
                title="Status Penghapusan Akun"
                description="Status pengajuan penghapusan akun Anda"
            />
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-5 dark:border-amber-900/40 dark:bg-amber-950/10 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                        <Clock class="w-5 h-5 animate-pulse" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Pengajuan Penghapusan Sedang Diproses</h4>
                        <p class="text-[13px] text-amber-700 dark:text-amber-300 mt-0.5 leading-relaxed">
                            Akun Anda diajukan untuk dihapus pada <span class="font-semibold">{{ formatDate(user.deletion_requested_at) }}</span>. 
                            Mohon tunggu persetujuan administrator di pengaturan WorkOS.
                        </p>
                    </div>
                </div>
                <Button 
                    variant="outline" 
                    @click="cancelRequest"
                    :disabled="cancelForm.processing"
                    class="border-amber-200 hover:bg-amber-100/50 text-amber-700 hover:text-amber-800 dark:border-amber-900/60 dark:hover:bg-amber-950/20 dark:text-amber-300 dark:hover:text-amber-200"
                >
                    <Loader2 v-if="cancelForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <Ban v-else class="mr-2 h-4 w-4" />
                    Batalkan Pengajuan
                </Button>
            </div>
        </div>

        <!-- Case 2: Deletion not requested yet -->
        <div v-else class="space-y-6">
            <Heading
                variant="small"
                title="Ajukan Penghapusan Akun"
                description="Ajukan penghapusan akun Anda beserta seluruh data di dalamnya"
            />
            <div
                class="space-y-4 rounded-xl border border-red-100 bg-red-50/60 p-5 dark:border-red-200/10 dark:bg-red-700/5"
            >
                <div class="relative space-y-1 text-red-700 dark:text-red-300">
                    <p class="font-bold flex items-center gap-1.5 text-sm md:text-md">
                        <AlertTriangle class="w-4.5 h-4.5" />
                        Peringatan Penting
                    </p>
                    <p class="text-[13px] leading-relaxed">
                        Pengajuan penghapusan akun memerlukan verifikasi kata sandi dan persetujuan dari Administrator. 
                        Setelah admin menyetujui pengajuan ini, semua data Anda akan dihapus secara permanen dan tidak dapat dipulihkan.
                    </p>
                </div>
                
                <Dialog v-model:open="isOpen">
                    <DialogTrigger as-child>
                        <Button variant="destructive" data-test="delete-user-button">
                            Ajukan Penghapusan Akun
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="submitRequest" class="space-y-6">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>Apakah Anda yakin ingin mengajukan penghapusan akun?</DialogTitle>
                                <DialogDescription>
                                    Setelah pengajuan disetujui oleh admin, semua data dan karya Anda akan dihapus permanen. 
                                    Silakan masukkan kata sandi Anda untuk mengonfirmasi pengajuan ini.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only">Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    v-model="requestForm.password"
                                    ref="passwordInput"
                                    placeholder="Kata Sandi Anda"
                                />
                                <InputError :message="requestForm.errors.password" />
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button
                                        variant="secondary"
                                        type="button"
                                        @click="() => {
                                            requestForm.reset();
                                            requestForm.clearErrors();
                                        }"
                                    >
                                        Batal
                                    </Button>
                                </DialogClose>

                                <Button
                                    type="submit"
                                    variant="destructive"
                                    :disabled="requestForm.processing"
                                    data-test="confirm-delete-user-button"
                                >
                                    <Loader2 v-if="requestForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                    Kirim Pengajuan
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
