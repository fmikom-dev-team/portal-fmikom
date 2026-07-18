import { ref, onMounted, onUnmounted } from 'vue';

const isInstallable = ref(false);
const isInstalled = ref(false);
let deferredPrompt: any = null;

export function usePwaInstall() {
    const checkInstalled = () => {
        if (typeof window !== 'undefined') {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                || (window.navigator as any).standalone 
                || document.referrer.includes('android-app://');
            isInstalled.value = !!isStandalone;
        }
    };

    const handleBeforeInstallPrompt = (e: Event) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        // Update UI notify the user they can install the PWA
        isInstallable.value = true;
    };

    const handleAppInstalled = () => {
        // Clear the deferredPrompt so it can be garbage collected
        deferredPrompt = null;
        isInstallable.value = false;
        isInstalled.value = true;
        console.log('[PWA] App installed successfully');
    };

    const installPwa = async () => {
        if (!deferredPrompt) {
            return;
        }
        // Show the install prompt
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`[PWA] User response to the install prompt: ${outcome}`);
        if (outcome === 'accepted') {
            deferredPrompt = null;
            isInstallable.value = false;
        }
    };

    onMounted(() => {
        if (typeof window !== 'undefined') {
            checkInstalled();
            window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
            window.addEventListener('appinstalled', handleAppInstalled);
        }
    });

    onUnmounted(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
            window.removeEventListener('appinstalled', handleAppInstalled);
        }
    });

    return {
        isInstallable,
        isInstalled,
        installPwa,
    };
}
