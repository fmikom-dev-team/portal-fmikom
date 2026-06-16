<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
    FileText,
    MoreVertical,
    Edit2,
    TrendingUp,
    AlertTriangle,
    Copy,
    Trash2,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const props = defineProps<{
    kuesioner: {
        id: number;
        judul: string;
        subtitle?: string;
        kategori?: string;
        tahun?: number;
        status: string;
        responses_count: number;
        created_at: string;
        date_mulai?: string;
        date_selesai?: string;
    };
}>();

const questionnaireStatus = computed(() => {
    const status = props.kuesioner.status;
    const now = new Date();

    // Draft: if status is not active/published
    if (status !== "active" && status !== "published") {
        return {
            label: "Draft",
            variant: "secondary" as const,
            badgeClass:
                "bg-slate-100 text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-700",
            dotClass: "bg-slate-500 dark:bg-slate-400",
            borderColorClass: "bg-slate-400",
            type: "draft",
        };
    }

    // Terjadwal (Scheduled): if status is active but date_mulai is in the future.
    if (props.kuesioner.date_mulai) {
        const start = new Date(props.kuesioner.date_mulai);

        if (start > now) {
            return {
                label: "Terjadwal",
                variant: "outline" as const,
                badgeClass:
                    "bg-blue-50 text-blue-700 hover:bg-blue-50 dark:bg-blue-950/40 dark:text-blue-400 dark:hover:bg-blue-950/40 border-blue-200 dark:border-blue-800/60",
                dotClass: "bg-blue-600 dark:bg-blue-400",
                borderColorClass: "bg-blue-500",
                type: "scheduled",
            };
        }
    }

    // Selesai (Completed): if status is active but date_selesai is in the past.
    if (props.kuesioner.date_selesai) {
        const end = new Date(props.kuesioner.date_selesai);
        // Treat end date as inclusive of that entire day (end of day local time)
        end.setHours(23, 59, 59, 999);

        if (end < now) {
            return {
                label: "Selesai",
                variant: "secondary" as const,
                badgeClass:
                    "bg-amber-100 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/20 dark:text-amber-400 dark:hover:bg-amber-500/20 border-amber-200 dark:border-amber-800/60",
                dotClass: "bg-amber-600 dark:bg-amber-400",
                borderColorClass: "bg-amber-500",
                type: "completed",
            };
        }
    }

    // Aktif (Active): if status is active and current date falls within start/end dates
    return {
        label: "Aktif",
        variant: "default" as const,
        badgeClass:
            "bg-green-100 text-green-700 hover:bg-green-100 dark:bg-green-500/20 dark:text-green-400 dark:hover:bg-green-500/20 border-green-200 dark:border-green-800/60",
        dotClass: "bg-green-600 dark:bg-green-400",
        borderColorClass: "bg-green-500",
        type: "active",
    };
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        month: "short",
        day: "2-digit",
        year: "numeric",
    });
};

const editQuestionnaire = () => {
    router.get(`/trace/admin/questionnaires/${props.kuesioner.id}`);
};

const viewAnalytics = () => {
    router.get(`/trace/admin/questionnaires/${props.kuesioner.id}/analytics-page`);
};

const showDuplicateDialog = ref(false);
const showDeleteDialog = ref(false);

const duplicateQuestionnaire = () => {
    showDuplicateDialog.value = false;
    router.post(`/trace/admin/questionnaires/${props.kuesioner.id}/duplicate`);
};

const deleteQuestionnaire = () => {
    showDeleteDialog.value = false;
    router.delete(`/trace/admin/questionnaires/${props.kuesioner.id}`);
};
</script>

<template>
    <div
        class="group relative flex items-center gap-6 rounded-2xl border border-border/40 bg-card p-5 transition-all hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5 dark:bg-card/50"
    >
        <div
            v-if="questionnaireStatus.type !== 'draft'"
            class="absolute top-1/2 left-0 h-12 w-1 -translate-y-1/2 rounded-r-full"
            :class="
                questionnaireStatus.type === 'active'
                    ? 'bg-green-500'
                    : questionnaireStatus.type === 'scheduled'
                      ? 'bg-blue-500'
                      : 'bg-amber-500'
            "
        ></div>

        <div
            class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#0C447C]/10 text-[#0C447C] dark:bg-[#0C447C]/10 dark:text-[#85B7EB]"
        >
            <FileText class="h-7 w-7" />
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <h3 class="truncate text-lg font-bold text-foreground">
                    {{ kuesioner.judul }}
                </h3>
            </div>
            <p class="text-sm text-muted-foreground">
                <span v-if="kuesioner.tahun" class="font-medium">{{
                    kuesioner.tahun
                }}</span>
                <span v-if="kuesioner.tahun && kuesioner.kategori" class="mx-1"
                    >·</span
                >
                <span v-if="kuesioner.kategori">{{ kuesioner.kategori }}</span>
                <template v-if="!kuesioner.tahun && !kuesioner.kategori"
                    >Kuesioner Umum</template
                >
            </p>
        </div>

        <div class="hidden w-32 text-center md:block">
            <Badge
                :variant="questionnaireStatus.variant"
                class="px-3 py-1 font-medium capitalize border"
                :class="questionnaireStatus.badgeClass"
            >
                <div
                    class="mr-1.5 h-1.5 w-1.5 rounded-full"
                    :class="questionnaireStatus.dotClass"
                ></div>
                {{ questionnaireStatus.label }}
            </Badge>
        </div>

        <div class="hidden w-36 flex-col items-center lg:flex">
            <span class="text-lg font-bold text-foreground">{{
                kuesioner.responses_count.toLocaleString()
            }}</span>
            <span
                class="text-[10px] font-bold tracking-wider uppercase text-muted-foreground/60"
            >
                Responden
            </span>
        </div>

        <div class="hidden w-40 text-right xl:block">
            <span class="text-sm text-muted-foreground">{{
                formatDate(kuesioner.created_at)
            }}</span>
        </div>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="icon"
                class="h-10 w-10 rounded-xl border-dashed border-primary/30 text-primary hover:bg-primary/5"
                @click="viewAnalytics"
                title="Lihat Analisis"
            >
                <TrendingUp class="h-4 w-4" />
            </Button>

            <Button
                variant="outline"
                size="icon"
                class="h-10 w-10 rounded-xl border-dashed border-primary/30 text-primary hover:bg-primary/5"
                @click="editQuestionnaire"
            >
                <Edit2 class="h-4 w-4" />
            </Button>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 text-muted-foreground"
                    >
                        <MoreVertical class="h-5 w-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-48">
                    <DropdownMenuItem @click="viewAnalytics">
                        Lihat Analisis
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="editQuestionnaire">
                        Edit kuesioner
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="showDuplicateDialog = true">
                        Duplikat kuesioner
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        class="text-destructive"
                        @click="showDeleteDialog = true"
                    >
                        Hapus kuesioner
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <!-- Duplicate Confirmation -->
        <Dialog v-model:open="showDuplicateDialog">
            <DialogContent class="max-w-md rounded-2xl">
                <DialogHeader>
                    <div
                        class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#0C447C]/10 dark:bg-[#0C447C]/20"
                    >
                        <Copy class="h-5 w-5 text-[#0C447C] dark:text-[#85B7EB]" />
                    </div>
                    <DialogTitle class="text-center"
                        >Duplikat Kuesioner</DialogTitle
                    >
                    <DialogDescription class="text-center">
                        Kuesioner <strong>{{ kuesioner.judul }}</strong> akan
                        diduplikasi beserta semua bagian dan pertanyaannya.
                        Responden tidak akan ikut terduplikasi.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        @click="showDuplicateDialog = false"
                        >Batal</Button
                    >
                    <Button
                        class="bg-[#0C447C] hover:bg-[#0C447C]/90 text-white"
                        @click="duplicateQuestionnaire"
                    >
                        Ya, Duplikat
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="max-w-md rounded-2xl">
                <DialogHeader>
                    <div
                        class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-950/30"
                    >
                        <Trash2 class="h-5 w-5 text-red-600" />
                    </div>
                    <DialogTitle class="text-center"
                        >Hapus Kuesioner</DialogTitle
                    >
                    <DialogDescription class="text-center">
                        Anda yakin ingin menghapus
                        <strong>{{ kuesioner.judul }}</strong
                        >? Semua data responden yang terkait juga akan terhapus.
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button variant="outline" @click="showDeleteDialog = false"
                        >Batal</Button
                    >
                    <Button
                        class="bg-red-600 hover:bg-red-700 text-white"
                        @click="deleteQuestionnaire"
                    >
                        Ya, Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
