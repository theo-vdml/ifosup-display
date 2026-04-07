import { ref, onBeforeUnmount } from 'vue';

export function useSyncScroll() {
    // Références aux éléments du DOM
    const headerTrackRef = ref<HTMLElement | null>(null);
    const sidebarTrackRef = ref<HTMLElement | null>(null);
    const gridScrollerRef = ref<HTMLElement | null>(null);

    // État interne pour la gestion des performances
    let animationFrameId: number | null = null;
    let pendingLeft = 0;
    let pendingTop = 0;
    let appliedLeft = -1;
    let appliedTop = -1;

    /**
     * Applique les transformations CSS de manière synchronisée
     */
    const syncTracks = () => {
        if (pendingLeft === appliedLeft && pendingTop === appliedTop) {
            animationFrameId = null;
            return;
        }

        // On utilise transform pour de meilleures performances (accélération GPU)
        if (headerTrackRef.value) {
            headerTrackRef.value.style.transform = `translateX(-${pendingLeft}px)`;
        }

        if (sidebarTrackRef.value) {
            sidebarTrackRef.value.style.transform = `translateY(-${pendingTop}px)`;
        }

        appliedLeft = pendingLeft;
        appliedTop = pendingTop;
        animationFrameId = null;
    };

    /**
     * Handler principal à attacher à l'événement @scroll de la grille
     */
    const onGridScroll = (event: Event) => {
        const target = event.target as HTMLElement;
        pendingLeft = Math.round(target.scrollLeft);
        pendingTop = Math.round(target.scrollTop);

        if (pendingLeft === appliedLeft && pendingTop === appliedTop) {
            return;
        }

        if (animationFrameId !== null) {
            return;
        }

        // On planifie la mise à jour visuelle pour le prochain rafraîchissement d'écran
        animationFrameId = requestAnimationFrame(syncTracks);
    };

    /**
     * Permet de forcer une synchronisation (utile après un changement de zoom ou au montage)
     */
    const forceSync = () => {
        if (gridScrollerRef.value) {
            pendingLeft = Math.round(gridScrollerRef.value.scrollLeft);
            pendingTop = Math.round(gridScrollerRef.value.scrollTop);
            syncTracks();
        }
    };

    onBeforeUnmount(() => {
        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
        }
    });

    return {
        headerTrackRef,
        sidebarTrackRef,
        gridScrollerRef,
        onGridScroll,
        forceSync,
    };
}
