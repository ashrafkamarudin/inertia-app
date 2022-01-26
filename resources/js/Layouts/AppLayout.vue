<template>
    <div style="font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji;">
        <Head :title="title" />

        <div class="navbar navbar-default">
            <div class="w-2/12">
                <a href="https://new.runcloud.test/servers"><img alt="Avatar" class="px-1 mr-2 img-avatar" height="48px" src="/runcloud/img/rc-logo-white-icon.svg" width="48px"></a>
            </div>
            <div class="w-64 md:w-84 lg:w-120">
            </div>
            <div class="w-2/12 md:w-2/12">
                <div class="space-x-6 navbar-menu" style="">
                    <div class="flex-shrink-0 navbar-item">
                        <div class="md:relative dropdown" data-v-1061b0a0="">
                            <a aria-expanded="false" aria-haspopup="true" class="dropdown-toggle d-none d-md-block" data-toggle="dropdown" data-v-1061b0a0="" href="#">
                            <div class="image-holder relative left-0 top-0" data-v-1061b0a0="">
                                <div class="flex-shrink-0" data-v-1061b0a0=""><img class="rounded-full flex-shrink-0" src="https://www.gravatar.com/avatar/ed577fe3e3c19a96f590e822f231a24b?s=80&amp;d=identicon&amp;r=g" style="height: 40px; width: 40px;"></div><!---->
                            </div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid justify-items-center pt-16 min-h-screen">
            <!-- Page Content -->
            <main class="relative w-8/12 justify-center p-4 3xl:mx-auto 3xl:max-w-screen-2xl md:px-7 md:py-7 md:pb-16 z-1 ">
                <section class="relative w-8/12 section-header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <h1 class="whitespace-pre-wrap break-words">{{ title }}</h1>
                        </div><!---->
                        <div class="action-header" id="actionMdUp" style="">
                            <ul id="desktopMenu">
                                <li style="display: none;">
                                    <div class="dropdown">
                                        <button class="dropdown-toggle btn btn-default icon-only" data-toggle="dropdown"><i class="rc rc-ln-more"></i></button>
                                        <ul class="min-w-48 dropdown-menu dropdown-menu-right" id="desktopMore"></ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="action-header" id="actionMdLow" style="display: none;">
                            <div class="dropdown" style="display: none;">
                                <button class="dropdown-toggle btn btn-default icon-only" data-toggle="dropdown"><i class="rc rc-ln-more"></i></button>
                                <ul class="min-w-48 dropdown-menu dropdown-menu-right" id="mobileMore"></ul>
                            </div>
                        </div>
                    </div><!---->
                </section>

                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script>
    import { defineComponent } from 'vue'
    import { Head, Link } from '@inertiajs/inertia-vue3';

    export default defineComponent({
        props: {
            title: String,
        },

        components: {
            Head,
            Link,
        },

        data() {
            return {
                showingNavigationDropdown: false,
            }
        },

        methods: {
            logout() {
                this.$inertia.post(route('logout'));
            },
        }
    })
</script>
