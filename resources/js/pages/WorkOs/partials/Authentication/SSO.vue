<script setup lang="ts">
import { Check, Plus, ShieldCheck } from "lucide-vue-next";
</script>

<template>
    <div class="space-y-6 animate-fade-in max-w-[800px]">
        <!-- Header -->
        <div class="border-b border-gray-200 pb-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Single Sign-On</h1>
                <p class="text-[14px] text-gray-500 mt-1">Configure SAML and OpenID Connect (OIDC) identity providers for your enterprise customers.</p>
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-md text-[13px] font-medium hover:bg-gray-800 transition-colors shadow-sm flex items-center justify-center gap-2 shrink-0">
                <Plus class="w-4 h-4" /> Add Connection
            </button>
        </div>

        <!-- Empty State -->
        <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-sm text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <ShieldCheck class="w-6 h-6 text-gray-400" />
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-1">No SSO connections yet</h3>
            <p class="text-[13px] text-gray-500 mb-6 max-w-sm mx-auto">Create an SSO connection to allow your users to authenticate using their enterprise identity provider.</p>
            <button class="px-4 py-2 bg-white border border-gray-300 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                View Documentation
            </button>
        </div>

        <!-- Features list -->
        <div class="pt-4">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Supported Identity Providers</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="idp in ['Okta', 'Azure AD', 'Google Workspace', 'OneLogin', 'PingIdentity', 'JumpCloud', 'CyberArk', 'Generic SAML']" :key="idp" class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <Check class="w-4 h-4 text-emerald-500 shrink-0" />
                    <span class="text-[13px] font-medium text-gray-700 truncate">{{ idp }}</span>
                </div>
            </div>
        </div>
    </div>
</template>