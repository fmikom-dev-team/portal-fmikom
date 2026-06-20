<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import DeleteUser from "@/components/DeleteUser.vue";
import ProfileEditor from "@/components/ProfileEditor.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import SettingsLayout from "@/layouts/settings/Layout.vue";
import { edit as editProfile } from "@/routes/profile";
import type { BreadcrumbItem } from "@/types";

type Props = {
	mustVerifyEmail: boolean;
	status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
	{
		title: "Profile settings",
		href: editProfile(),
	},
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head>
        <title>Profile settings</title>
    </Head>

        <SettingsLayout>
            <!-- Profile Editor Form Component -->
            <ProfileEditor 
                submit-url="/settings/profile" 
                hide-header 
                use-simple-container
            />

            <!-- Delete account segment -->
            <div class="max-w-2xl border-t border-slate-100 dark:border-slate-800/80 pt-8 mt-8">
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
