<template>
  <span class="badge" :class="badgeClass">
    <span class="w-1.5 h-1.5 rounded-full" :class="dotClass"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String, label: String });

const map = {
  pending:    { badge: 'badge-pending',    dot: 'bg-amber-500' },
  received:   { badge: 'badge-received',   dot: 'bg-blue-500' },
  preparing:  { badge: 'badge-preparing',  dot: 'bg-purple-500' },
  delivering: { badge: 'badge-delivering', dot: 'bg-cyan-500' },
  delivered:  { badge: 'badge-delivered',  dot: 'bg-emerald-500' },
};

const badgeClass = computed(() => map[props.status]?.badge ?? 'badge bg-slate-100 text-slate-600');
const dotClass   = computed(() => map[props.status]?.dot   ?? 'bg-slate-400');
</script>
