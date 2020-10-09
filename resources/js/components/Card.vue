<template>
    <card class="card relative px-6 py-4 card-panel">
        <loading-view :loading="loading" class="w-full">
            <div class="flex flex-row justify-between mb-4">
                <div>
                    <h3 class="mr-3 text-base text-80 font-bold">{{ __('novaStorageInfoCard.diskControl') }}</h3>
                </div>
                <div>
                    <button @click="refresh"
                            :title="__('novaStorageInfoCard.refreshButtonHelpText')"
                            class="text-primary outline-none focus:outline-none flex flex-row items-center text-white rounded">
                        <span class="mr-2">{{ __('novaStorageInfoCard.refresh') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                            <path
                                d="M20.944 12.979c-.489 4.509-4.306 8.021-8.944 8.021-2.698 0-5.112-1.194-6.763-3.075l1.245-1.633c1.283 1.645 3.276 2.708 5.518 2.708 3.526 0 6.444-2.624 6.923-6.021h-2.923l4-5.25 4 5.25h-3.056zm-15.864-1.979c.487-3.387 3.4-6 6.92-6 2.237 0 4.228 1.059 5.51 2.698l1.244-1.632c-1.65-1.876-4.061-3.066-6.754-3.066-4.632 0-8.443 3.501-8.941 8h-3.059l4 5.25 4-5.25h-2.92z"
                                fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="!initialLoading" class="w-full text-black">
                <vue-tiny-slider
                    :mouse-drag="true"
                    :loop="true"
                    :autoplay="card.autoplay"
                    :autoplayButton="false"
                    :autoplayButtonOutput="false"
                    :autoplayTimeout="10000"
                    :items="sliderItems"
                    gutter="20"
                    :controls="false"
                    :autoplayHoverPause="true"
                    :dots="true"
                    navPosition="bottom">
                    <li v-for="(disk, i) in disks" :key="i">
                        <div class="disk-item flex flex-row justify-between text-black-50 pb-2 py-6"
                             :class="{'cursor-grab' : disks.length > 1}">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{disk.title}}</span>
                                <span class="w-full font-light">{{ disk.bucket }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="w-full font-semibold text-right">{{ disk.size }} <span v-if="disk.space">/ {{ disk.space }}</span></span>
                                <span class="w-full font-light text-right">{{ disk.items }}</span>
                            </div>
                        </div>
                    </li>
                </vue-tiny-slider>

            </div>

        </loading-view>
    </card>
</template>

<script>
    import VueTinySlider from 'vue-tiny-slider';

    export default {
        components: {VueTinySlider},
        props: ['card'],


        data() {
            return {
                loading: true,
                disks: [],
            }
        },

        mounted() {
            this.load();
        },

        computed: {
            sliderItems() {
                switch (this.card.width) {
                    case '1/2':
                    case '2/3':
                    case '3/4':
                        return 2;
                    case 'full':
                        return 3;
                    default:
                        return 1;
                }
            }
        },

        methods: {
            refresh() {
                return Nova.request().post('/nova-vendor/nova-storage-info-card/refresh', {
                    disks: this.card.disks
                }).then((res) => {
                    this.load();
                });
            },

            load() {
                /** Set loading indicator */
                this.loading = true;

                /** Clear all disks */
                this.disks = [];

                /** Load all disks information buckets */
                Promise.all(
                    this.card.disks.map((d, i) => {
                        return this.loadStats(d, i, this.card.cacheFor)
                    })
                ).then(() => {
                    this.loading = false;
                })
            },

            loadStats(disk, index, cache = null) {
                return Nova.request().post('/nova-vendor/nova-storage-info-card/stats', {
                    disk: disk.name,
                    cache: cache
                }).then((res) => {
                    this.disks[index] = {
                        title: disk.title,
                        space: disk.space,
                        bucket: res.data.bucket,
                        size: res.data.size,
                        items: res.data.items,
                    };

                });
            },
        },
    }
</script>
