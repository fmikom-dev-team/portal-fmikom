<script setup lang="ts">
import axios from 'axios';
import { toast } from 'vue-sonner';
import { Loader2, Mail, Calendar, User } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';

const props = defineProps<{
    kuesionerId: number | string;
}>();

const formatDate = (dateString: string) => {
    if (!dateString) {
return '-';
}

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).format(new Date(dateString));
};

const formatTime = (dateString: string) => {
    if (!dateString) {
return '';
}

    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(dateString));
};

const loading = ref(true);
const respondents = ref<any[]>([]);

const fetchRespondents = async () => {
    loading.value = true;

    try {
        const response = await axios.get(`/trace/admin/questionnaires/${props.kuesionerId}/respondents`);
        respondents.value = response.data.data;
    } catch (error) {

        toast.error('Gagal memuat daftar responden.');
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRespondents);

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
};
</script>

<template>
    <div class="space-y-4">
        <div 
            v-if="loading" 
            class="flex h-64 flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-100 bg-white/50"
        >
            <Loader2 class="h-8 w-8 animate-spin text-blue-500" />
            <p class="mt-4 text-sm font-medium text-muted-foreground">Memuat daftar responden...</p>
        </div>

        <div v-else class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-950/50">
            <Table>
                <TableHeader class="bg-slate-50/50 dark:bg-slate-900/50">
                    <TableRow>
                        <TableHead class="h-14 font-black tracking-widest text-[10px] uppercase">Nama Responden</TableHead>
                        <TableHead class="h-14 font-black tracking-widest text-[10px] uppercase text-center">NIM</TableHead>
                        <TableHead class="h-14 font-black tracking-widest text-[10px] uppercase">Email</TableHead>
                        <TableHead class="h-14 font-black tracking-widest text-[10px] uppercase">Tahun Lulus</TableHead>
                        <TableHead class="h-14 font-black tracking-widest text-[10px] uppercase text-right">Tanggal Mengisi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="res in respondents" :key="res.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="h-9 w-9 border border-white shadow-sm">
                                    <AvatarFallback class="bg-blue-600 text-white text-[10px] font-black">
                                        {{ getInitials(res.user?.alumni_profile?.nama_lengkap || res.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold">{{ res.user?.alumni_profile?.nama_lengkap || res.user.name }}</span>
                                    <span class="text-[10px] text-muted-foreground font-medium">{{ res.user?.alumni_profile?.program_studi || 'Alumni' }}</span>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell class="text-center font-mono text-xs font-bold text-slate-500">
                            {{ res.user?.alumni_profile?.nim || '-' }}
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <Mail class="h-3 w-3 opacity-40" />
                                {{ res.user.email }}
                            </div>
                        </TableCell>
                        <TableCell>
                             <Badge variant="outline" class="rounded-full bg-slate-100 text-[10px] font-bold border-none">
                                {{ res.user?.alumni_profile?.tahun_lulus || res.angkatan || '-' }}
                             </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-xs font-bold">{{ formatDate(res.submitted_at || res.created_at) }}</span>
                                <span class="text-[10px] text-muted-foreground font-medium">{{ formatTime(res.submitted_at || res.created_at) }} WIB</span>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="respondents.length === 0">
                        <TableCell colspan="5" class="h-32 text-center text-muted-foreground font-medium">
                            Belum ada responden yang mengisi kuesioner ini.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
