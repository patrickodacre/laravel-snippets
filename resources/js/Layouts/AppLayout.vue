<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link } from '@inertiajs/inertia-vue3';
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import JetBanner from '@/Jetstream/Banner.vue';
import JetDropdown from '@/Jetstream/Dropdown.vue';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import JetNavLink from '@/Jetstream/NavLink.vue';
import JetResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    Inertia.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    Inertia.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <JetBanner />

        <div
            class="bg-light min-vh-100"
        >
            <nav class="bg-white border-light">
                <!-- Primary Navigation Menu -->
                <div class="ln-max-width mx-auto px-4 px-sm-6 px-lg-8">
                    <div class="flex justify-content-between h-16">
                        <div class="d-flex align-items-center">
                            <!-- Logo -->
                            <div class="flex-shrink-0 me-5">
                                <Link :href="route('dashboard')">
                                    <JetApplicationMark class="d-block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="mb-sm-10 d-flex justify-content-evenly h-100">
                                <JetNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </JetNavLink>
                                <JetNavLink :href="route('clients')" :active="route().current('clients')">
                                    Clients
                                </JetNavLink>

                                <JetNavLink :href="route('tags')" :active="route().current('tags')">
                                    Tags
                                </JetNavLink>

                                <JetNavLink :href="route('clients_grid')" :active="route().current('clients_grid')">
                                    Clients Grid
                                </JetNavLink>


                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow mb-5">
                <div class="ln-max-width mx-auto py-2 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

        </div>
    </div>

</template>
