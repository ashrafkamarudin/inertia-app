<template>
    <app-layout title="Server">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="min-w-screen panel panel-default mb-0" style="border-radius: 8px 8px 0 0 !important;">
                <div class="panel-heading" style="border-bottom-width: 0px !important;">
                    <div data-v-ba069038="">
                        <div class="flex flex-wrap flex-col md:items-baseline justify-between flex-grow w-full space-y-4 md:space-y-0 md:flex-row md:flex-grow-0 mix-slot" data-v-ba069038="">
                            <!---->
                            <div class="flex-grow w-full filter-group filter md:w-auto md:flex-grow-0 filter-slot" data-v-ba069038="">
                                <div class="flex-grow flex-col md:flex-row md:flex-grow-0 text-center hidden md:flex pb-4 xl:pb-0" data-v-ba069038="">
                                    <div class="md:flex md:items-center md:content-between form-group form-search" data-v-41c6da32="" data-v-ba069038="">
                                        <i class="pl-3 left-0 opacity-75 rc rc-ln-search rc-icon absolute left-icon transform top-1/2 -translate-y-1/2" data-v-41c6da32=""></i> <!----> 
                                        <input class="form-control pl-8 border-gray-500" data-v-41c6da32="" placeholder="Search..." type="text" v-model.lazy="searchValue" @change="search"> <!----> <!---->
                                    </div>
                                    <div class="form-group w-auto" data-v-ba069038="">
                                        <!----><!----><!---->
                                        <div class="multiselect w-auto multiselect-black" tabindex="-1">
                                            <div class="multiselect__select"></div>
                                            <div class="multiselect__tags">
                                                <div class="multiselect__tags-wrap" style="display: none;"></div><!---->
                                                <div class="multiselect__spinner" style="display: none;"></div><input autocomplete="nope" class="multiselect__input" name="selectfilter" placeholder="All Servers" style="width: 0px; position: absolute; padding: 0px;" tabindex="0" type="text"> <!----> <span class="multiselect__placeholder">All Servers</span>
                                            </div>
                                            <div class="multiselect__content-wrapper" style="max-height: 300px; display: none;" tabindex="-1">
                                                <ul class="multiselect__content" style="display: inline-block;">
                                                    <!---->
                                                    <li class="multiselect__element">
                                                        <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected"></span>
                                                        <div class="flex">
                                                            <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected"><!----> <!----></span>
                                                            <div class="whitespace-pre-wrap md:whitespace-nowrap pb-1 md:pb-0">
                                                                <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected">My Servers</span>
                                                            </div>
                                                        </div><!---->
                                                    </li>
                                                    <li class="multiselect__element">
                                                        <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected"></span>
                                                        <div class="flex">
                                                            <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected"><!----> <!----></span>
                                                            <div class="whitespace-pre-wrap md:whitespace-nowrap pb-1 md:pb-0">
                                                                <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected">Shared Servers</span>
                                                            </div>
                                                        </div><!---->
                                                    </li>
                                                    <li style="display: none;"><span class="multiselect__option">No elements found. Consider changing the search query.</span></li>
                                                    <li style="display: none;"><span class="multiselect__option">List is empty.</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="form-input-description" style="display: none;"></p><!---->
                                    </div>
                                    <div class="dropdown" data-v-ba069038="">
                                        <button aria-expanded="false" aria-haspopup="true" class="btn btn-default" data-toggle="dropdown" data-v-ba069038="" role="button"><span class="inline-block text-black-500 font-normal text-base align-text-bottom" data-v-ba069038="">Tags <i class="rc rc-gl-caret text-gray-700 ml-3" data-v-ba069038="" style="font-size: 10px; color: rgb(153, 153, 153);"></i></span></button>
                                        <div class="dropdown-menu flex-wrap gap-2 w-80 flex-div max-h-screen overflow-y-auto rounded-tl-none" data-v-ba069038="" role="menu" style="margin-top: -0.5px; margin-left: -1px;">
                                            <p class="text-center px-2" data-v-ba069038="">No server tags available.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-grow flex-col text-center md:hidden" data-v-ba069038="">
                                    <div class="md:flex md:items-center md:content-between form-group form-search" data-v-41c6da32="" data-v-ba069038="">
                                        <i class="left-0 opacity-75 rc rc-ln-search rc-icon absolute left-icon transform top-1/2 -translate-y-1/2" data-v-41c6da32=""></i> <!----> <input class="form-control" data-v-41c6da32="" placeholder="Search..." type="text"> <!----> <!---->
                                    </div>
                                    <div class="flex flex-col flex-grow pt-2" data-v-ba069038="">
                                        <div class="pb-2" data-v-ba069038="">
                                            <div class="form-group w-auto" data-v-ba069038="">
                                                <!----><!----><!---->
                                                <div class="multiselect w-auto" tabindex="-1">
                                                    <div class="multiselect__select"></div>
                                                    <div class="multiselect__tags">
                                                        <div class="multiselect__tags-wrap" style="display: none;"></div><!---->
                                                        <div class="multiselect__spinner" style="display: none;"></div><input autocomplete="nope" class="multiselect__input" name="selectfilter" placeholder="All Servers" style="width: 0px; position: absolute; padding: 0px;" tabindex="0" type="text"> <!----> <span class="multiselect__placeholder">All Servers</span>
                                                    </div>
                                                    <div class="multiselect__content-wrapper" style="max-height: 300px; display: none;" tabindex="-1">
                                                        <ul class="multiselect__content" style="display: inline-block;">
                                                            <!---->
                                                            <li class="multiselect__element">
                                                                <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected"></span>
                                                                <div class="flex">
                                                                    <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected"><!----> <!----></span>
                                                                    <div class="whitespace-pre-wrap md:whitespace-nowrap pb-1 md:pb-0">
                                                                        <span class="multiselect__option multiselect__option--highlight" data-deselect="Press enter to remove" data-select="" data-selected="Selected">My Servers</span>
                                                                    </div>
                                                                </div><!---->
                                                            </li>
                                                            <li class="multiselect__element">
                                                                <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected"></span>
                                                                <div class="flex">
                                                                    <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected"><!----> <!----></span>
                                                                    <div class="whitespace-pre-wrap md:whitespace-nowrap pb-1 md:pb-0">
                                                                        <span class="multiselect__option" data-deselect="Press enter to remove" data-select="" data-selected="Selected">Shared Servers</span>
                                                                    </div>
                                                                </div><!---->
                                                            </li>
                                                            <li style="display: none;"><span class="multiselect__option">No elements found. Consider changing the search query.</span></li>
                                                            <li style="display: none;"><span class="multiselect__option">List is empty.</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="form-input-description" style="display: none;"></p><!---->
                                            </div>
                                        </div>
                                        <div class="dropdown" data-v-ba069038="">
                                            <button aria-expanded="false" aria-haspopup="true" class="dropdown-toggle btn btn-default justify-between" data-toggle="dropdown" data-v-ba069038="" role="button" style="border-top-left-radius: 4px; border-bottom-left-radius: 4px; border-left-width: 1px;"><span class="inline-block text-gray-700 font-normal text-base pt-1" data-v-ba069038="">Tags</span> <i class="rc rc-gl-caret text-gray-700 pt-1" data-v-ba069038="" style="font-size: 10px; color: rgb(153, 153, 153);"></i></button>
                                            <div class="dropdown-menu flex-wrap gap-2 w-auto flex-div max-h-screen overflow-y-auto rounded-tl-none" data-v-ba069038="" role="menu" style="margin-top: -0.5px; margin-left: -1px;">
                                                <p class="text-center px-2" data-v-ba069038="">No server tags available.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center flex-shrink-0 w-full space-x-2 md:w-auto action-slot" data-v-ba069038="">
                                <div class="btn-group inline-flex" data-v-ba069038="">
                                    <button class="btn btn-default btn-max"><i class="rc rc-ln-grid-2" style="color: rgb(17, 17, 17);"></i></button> <button class="btn btn-default opacity-40"><i class="rc rc-ln-list-2" style="color: rgb(17, 17, 17);"></i></button>
                                </div><a class="btn btn-primary justify-center" data-v-ba069038="" href="https://manage.runcloud.io/servers/create" role="button">Connect a New Server</a>
                            </div>
                        </div><!---->
                    </div>
                </div>
            </div>
            <div class="min-w-screen panel bg-white border rounded-b-md">
                <div class="panel-table table-responsive pt-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Server</th>
                                <th width="15%">IP Address</th>
                                <th width="10%">Disk</th>
                                <th width="10%">RAM</th>
                                <th width="10%" class="text-center">Load</th>
                                <th>Tags</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr v-for="server in $page.props.servers.data" :key="server.id" class="group cursor-pointer">
                            <td class="w-3/12">
                                <div class="items-center no-scrollbar">
                                    <div class="flex items-center">
                                        <div class="bg-red-500 pt-2 h-2 w-2 rounded-full" v-if="!server.connected"></div>
                                        <div class="bg-blue-500 h-2 w-2 rounded-full"  v-else-if="server.changeIp"></div>
                                        <div class="bg-yellow-500 h-2 w-2 rounded-full" v-else-if="!server.online"></div>
                                        <div class="bg-green-500 h-2 w-2 rounded-full" v-else></div>
                                        <vps-icon :provider="server.provider" class="mx-4"></vps-icon>
                                        <a class="font-medium text-black-500 group-hover:text-blue-500" :href="server.url">
                                            {{ server.name }}
                                            <i class="rc rc-ln-info text-base" v-if="server.agentVersion && !server.isAgentLatestVersion"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="items-center no-scrollbar">
                                    <div class="flex flex-col">
                                        <div class="relative">
                                            <span :id="`ipAddress-${server.id}`" class="text-misc-700">{{ server.ipAddress }}</span>
                                            <a class="absolute overflow-visible btn-clipboard cursor-pointer text-sm mt-0.5 ml-1 opacity-0 group-hover:opacity-100 transition-all duration-100" @click.prevent.stop="toCopied(server.id)" :id="`ipAddress-${server.id}-copied`">Copy</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td colspan="3" v-if="!server.canViewServerHealth">Server information is not available on this plan. <br>Please <a :href="subscriptionUrl" @click="analyticEvent('Upgrade Plan', 'Click', 'Server Health')">upgrade</a> your plan.</td>
                            <td colspan="3" v-else-if="!server.connected || !server.online || _.isUndefined(server.latest_server_health)">Server information is not available.</td>
                            <td v-else>
                                <div class="items-center"><server-index-stats :used="server.latest_server_health.usedDiskSpace" :available="server.latest_server_health.availableDiskSpace" :total="server.latest_server_health.totalDiskSpace"></server-index-stats></div>
                            </td>
                            <td v-if="server.canViewServerHealth && server.connected && server.online && !_.isUndefined(server.latest_server_health)">
                                <div class="items-center"><server-index-stats :used="server.latest_server_health.usedMemory" :available="server.latest_server_health.availableMemory" :total="server.latest_server_health.totalMemory"></server-index-stats></div>
                            </td>
                            <td v-if="server.canViewServerHealth && server.connected && server.online && !_.isUndefined(server.latest_server_health)" :class="{ 'text-red-500': server.latest_server_health.loadAverage > 5 }" class="text-center">
                                <div class="items-center">{{ server.latest_server_health.loadAverage }}</div>
                            </td>
                            <td>
                                <Link :href="route('dashboard')">
                                    Add Tag
                                </Link>
                            </td>
                            <td>
                                <!-- <div class="dropdown" :class="toggleList[server.id] === true ? 'show' : ''">
                                    <a role="button"
                                    :id="`dropdownMenu-${server.id}`"
                                    @click.stop="toggleExpandMenu(server.id)"
                                    class="dropdown-toggle btn btn-default icon-only"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    :aria-expanded="toggleList[server.id]"
                                    v-tooltip.top="'Deploy, Settings'">
                                        <i class="rc rc-ln-more"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right" :class="toggleList[server.id] ? 'show' : ''" role="menu">
                                        <li><a @click.prevent.stop="goToUrl(server.url)" class="btn btn-primary btn-act"><i class="rc rc-ln-corner-right-round"></i><span>View</span></a></li>
                                        <li v-if="server.hasOwnProperty('tags')"><a class="btn btn-primary btn-act" @click.prevent.stop="$refs[`tagModal-${server.id}`][0].openModal()"><i class="rc rc-ln-tag"></i><span>{{ server.tags.length ? 'Edit' : 'Add' }} Tags</span></a></li>
                                        <li v-if="server.connected"><a @click.prevent.stop="goToUrl(webAppUrl.replace('=id=', server.id))" class="btn btn-primary btn-act"><i class="rc rc-ln-backup"></i><span>Deploy New Web App</span></a></li>
                                        <li v-if="server.connected"><a @click.prevent.stop="goToUrl(settingsUrl.replace('=id=', server.id))" class="btn btn-primary btn-act"><i class="rc rc-ln-gear"></i><span>Settings</span></a></li>
                                    </ul>
                                </div> -->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer flex justify-between">
                    <div>
                        <a @click="previousPage()" class="mr-2">Previous</a>
                        <a @click="nextPage()" class="mr-4">Next</a>
                    </div>
                    <a @click="reloadPage(true)" class="right-0">Reset</a>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import { defineComponent } from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import { Link } from '@inertiajs/inertia-vue3';

    export default defineComponent({
        components: {
            AppLayout,
            Link
        },

        data() {
            return {
                url: new URL(window.location.href),
                urlParams: new URLSearchParams(window.location.search),
                searchKey: 'search',
                searchValue: null,
            }
        },

        created () {
            let search = this.urlParams.get(this.searchKey)

            // append existing filter params
            for (let params of this.urlParams.entries()) {
                this.url.searchParams.set(params[0], params[1])
            }
        },

        methods: {
            search () {
                // set the new query params
                this.url.searchParams.set('scroll', window.scrollY)
                this.url.searchParams.set(this.searchKey, this.searchValue)

                this.reloadPage()
            },

            reloadPage(reset = false) {
                let url = this.url

                if (reset) {
                    return window.location.href = url.origin + url.pathname
                }

                return window.location.href = url.toString();
            },

            getCurrentPage() {
                let page = this.url.searchParams.get('page') ?? 1

                return parseInt(page)
            },

            nextPage() {
                this.url.searchParams.set('page', this.getCurrentPage()+1)
                this.reloadPage()
            },

            previousPage() {
                this.url.searchParams.set('page', this.getCurrentPage()-1)
                this.reloadPage()
            }
        }
    })
</script>
