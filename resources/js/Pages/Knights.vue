<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3';

defineProps({
    team: Object,
});
</script>

<template>
	{{ team }}
</template>
