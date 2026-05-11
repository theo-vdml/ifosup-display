<template>
    <div class="h-screen w-screen bg-black overflow-hidden relative">

        <transition name="slide" mode="out-in">
            <component v-if="currentSlide" :is="components[currentSlide.type]" :key="currentSlideKey"
                :data="currentSlide.data" @next="goToNextSlide" />
        </transition>
    </div>
</template>

<script setup lang="ts">
    import { computed, defineAsyncComponent, onMounted, onUnmounted, ref, watch } from 'vue';

    type SlideType = 'welcome' | 'schedule' | 'image' | 'video';

    interface AssignmentGroup {
        id?: number;
        name: string;
    }

    interface AssignmentTeacher {
        name: string;
    }

    interface AssignmentCourse {
        code?: string | null;
        name: string;
        teacher?: AssignmentTeacher | null;
        groups?: AssignmentGroup[];
    }

    interface AssignmentRoom {
        name: string;
    }

    interface AssignmentRow {
        id?: number;
        course?: AssignmentCourse | null;
        room?: AssignmentRoom | null;
    }

    interface WelcomeSlideData {
        minimumDuration?: number;
        isReady?: boolean;
        motd?: string | null;
    }

    interface ScheduleSlideData {
        title: string;
        rows: AssignmentRow[];
    }

    interface MediaSlideData {
        src: string;
        duration?: number;
    }

    type SlideData = WelcomeSlideData | ScheduleSlideData | MediaSlideData;

    interface KioskSlide {
        key: string;
        type: SlideType;
        data: SlideData;
    }

    interface ScreenPayload {
        now?: string;
        timezone?: string;
        slides: KioskSlide[];
    }

    const FALLBACK_WELCOME_SLIDE: KioskSlide = {
        key: 'welcome',
        type: 'welcome',
        data: {
            minimumDuration: 5000,
            isReady: true,
        },
    };

    const SCREEN_CACHE_KEY = 'screen:kiosk-payload:v1';
    const MEDIA_CACHE_NAME = 'screen:kiosk-media:v1';

    const components = {
        welcome: defineAsyncComponent(() => import('./slides/Welcome.vue')),
        image: defineAsyncComponent(() => import('./slides/ImageSlide.vue')),
        video: defineAsyncComponent(() => import('./slides/VideoSlide.vue')),
        schedule: defineAsyncComponent(() => import('./slides/ScheduleSlide.vue')),
    };

    const slides = ref<KioskSlide[]>([FALLBACK_WELCOME_SLIDE]);
    const isRefreshing = ref(false);
    let pendingRefresh: Promise<void> | null = null;
    const mediaObjectUrls = new Map<string, string>();

    const currentIndex = ref(0);
    const currentSlide = computed<KioskSlide | undefined>(() => slides.value[currentIndex.value]);
    const currentSlideKey = computed(() => currentSlide.value?.key ?? `slide-${currentIndex.value}`);

    function normalizePayload(payload: unknown): ScreenPayload {
        const candidate = payload as Partial<ScreenPayload> | null;

        return {
            now: typeof candidate?.now === 'string' ? candidate.now : undefined,
            timezone: typeof candidate?.timezone === 'string' ? candidate.timezone : undefined,
            slides: Array.isArray(candidate?.slides) ? candidate.slides as KioskSlide[] : [],
        };
    }

    function withWelcomeState(baseSlides: KioskSlide[], isReady: boolean): KioskSlide[] {
        return baseSlides.map((slide, index) => {
            if (index !== 0 || slide.type !== 'welcome') {
                return slide;
            }

            return {
                ...slide,
                data: {
                    ...(slide.data as WelcomeSlideData),
                    isReady,
                },
            };
        });
    }

    function normalizeMediaCacheKey(src: string): string {
        return new URL(src, window.location.origin).toString();
    }

    function readCachedPayload(): ScreenPayload | null {
        try {
            const raw = window.localStorage.getItem(SCREEN_CACHE_KEY);

            if (!raw) {
                return null;
            }

            return normalizePayload(JSON.parse(raw));
        } catch (error) {
            console.error('Unable to read cached screen data.', error);
            return null;
        }
    }

    function writeCachedPayload(payload: ScreenPayload): void {
        try {
            window.localStorage.setItem(SCREEN_CACHE_KEY, JSON.stringify(payload));
        } catch (error) {
            console.error('Unable to write cached screen data.', error);
        }
    }

    function isCacheableMediaSource(src: string): boolean {
        return /^(https?:\/\/|\/)/.test(src);
    }

    async function resolveCachedMediaSource(src: string): Promise<string> {
        if (!isCacheableMediaSource(src) || !('caches' in window)) {
            return src;
        }

        const cacheKey = normalizeMediaCacheKey(src);

        try {
            const cache = await window.caches.open(MEDIA_CACHE_NAME);
            const cachedResponse = await cache.match(cacheKey);

            if (!cachedResponse) {
                return src;
            }

            if (!mediaObjectUrls.has(cacheKey)) {
                const mediaBlob = await cachedResponse.blob();
                mediaObjectUrls.set(cacheKey, URL.createObjectURL(mediaBlob));
            }

            return mediaObjectUrls.get(cacheKey) ?? src;
        } catch (error) {
            console.error('Unable to resolve cached media.', error);
            return src;
        }
    }

    async function warmMediaCache(payloadSlides: KioskSlide[]): Promise<void> {
        if (!('caches' in window)) {
            return;
        }

        const mediaSlides = payloadSlides.filter((slide) => {
            if (!['image', 'video'].includes(slide.type)) {
                return false;
            }

            const data = slide.data as MediaSlideData;

            return typeof data.src === 'string' && data.src.length > 0;
        });

        if (mediaSlides.length === 0) {
            return;
        }

        try {
            const cache = await window.caches.open(MEDIA_CACHE_NAME);

            await Promise.all(mediaSlides.map(async (slide) => {
                const data = slide.data as MediaSlideData;
                const cacheKey = normalizeMediaCacheKey(data.src);

                if (await cache.match(cacheKey)) {
                    return;
                }

                try {
                    const response = await fetch(cacheKey);

                    if (!response.ok) {
                        return;
                    }

                    await cache.put(cacheKey, response.clone());
                } catch (error) {
                    console.error('Unable to cache media slide.', error);
                }
            }));
        } catch (error) {
            console.error('Unable to open media cache.', error);
        }
    }

    async function hydrateSlides(baseSlides: KioskSlide[], isReady: boolean): Promise<KioskSlide[]> {
        const hydratedSlides: KioskSlide[] = [];

        for (const slide of withWelcomeState(baseSlides, isReady)) {
            if (slide.type === 'image' || slide.type === 'video') {
                const data = slide.data as MediaSlideData;

                hydratedSlides.push({
                    ...slide,
                    data: {
                        ...data,
                        src: await resolveCachedMediaSource(data.src),
                    },
                });

                continue;
            }

            hydratedSlides.push(slide);
        }

        return hydratedSlides;
    }

    async function applyPayload(payload: ScreenPayload, isReady = true): Promise<void> {
        const baseSlides = payload.slides.length > 0 ? payload.slides : [FALLBACK_WELCOME_SLIDE];

        slides.value = await hydrateSlides(baseSlides, isReady);

        if (currentIndex.value >= slides.value.length) {
            currentIndex.value = 0;
        }
    }

    async function refreshAssignments(): Promise<void> {
        if (pendingRefresh) {
            return pendingRefresh;
        }

        isRefreshing.value = true;
        slides.value = withWelcomeState(slides.value, false);

        pendingRefresh = (async () => {
            try {
                const response = await fetch('/screen/data');

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                const payload = normalizePayload(await response.json());

                writeCachedPayload(payload);
                await applyPayload(payload, true);
                void warmMediaCache(payload.slides);
            } catch (error) {
                console.error('Unable to refresh screen data.', error);
                slides.value = withWelcomeState(slides.value, true);
            } finally {
                isRefreshing.value = false;
                pendingRefresh = null;
            }
        })();

        return pendingRefresh;
    }

    onMounted(async () => {
        const cachedPayload = readCachedPayload();

        if (cachedPayload) {
            await applyPayload(cachedPayload, true);
        }

        await refreshAssignments();
    });

    onUnmounted(() => {
        for (const objectUrl of mediaObjectUrls.values()) {
            URL.revokeObjectURL(objectUrl);
        }

        mediaObjectUrls.clear();
    });

    watch(currentIndex, async (newIndex, previousIndex) => {
        if (newIndex !== 0 || previousIndex === undefined || previousIndex === 0) {
            return;
        }

        await refreshAssignments();
    });

    async function goToNextSlide() {
        if (currentIndex.value === 0 && slides.value.length <= 1) {
            await refreshAssignments();

            if (slides.value.length <= 1) {
                return;
            }
        }

        currentIndex.value = (currentIndex.value + 1) % slides.value.length;
    }
</script>

<style scoped>
.slide-enter-active {
    transition: opacity 800ms ease, transform 800ms cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-leave-active {
    transition: opacity 400ms ease, transform 400ms ease;
    position: absolute;
    inset: 0;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(60px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-60px);
}
</style>
