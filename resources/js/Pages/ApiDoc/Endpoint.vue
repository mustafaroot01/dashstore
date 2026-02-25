<template>
  <div class="border rounded-xl mb-4 overflow-hidden shadow-sm transition-all duration-200" :class="[containerClass, {'ring-2 ring-opacity-20': isOpen, [ringClass]: isOpen}]">
    <!-- Header (Clickable) -->
    <div class="px-4 py-3 cursor-pointer flex items-center justify-between select-none" :class="headerClass" @click="isOpen = !isOpen">
      <div class="flex items-center gap-3 overflow-hidden">
        <span class="w-20 text-center px-2 py-1.5 rounded-md text-xs font-black text-white shrink-0 tracking-wider shadow-sm" :class="methodColor">
          {{ method }}
        </span>
        <code class="font-mono font-bold text-sm sm:text-base truncate" :class="textColor" dir="ltr">{{ path }}</code>
        <div class="hidden lg:flex items-center gap-2 ml-4 text-slate-500 text-sm truncate opacity-80" v-if="desc">
          <span>—</span>
          <span class="truncate">{{ desc }}</span>
        </div>
      </div>
      <div class="flex items-center gap-3 shrink-0 mr-4">
        <button @click.stop="copyPath" class="p-1.5 rounded-md transition-colors bg-white/50 hover:bg-white shadow-sm border border-transparent hover:border-slate-200" :class="textColor" title="نسخ المسار">
          <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </button>
        <div class="p-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300" :class="[textColor, {'rotate-180': isOpen}]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>
    </div>
    
    <!-- Body -->
    <div v-show="isOpen" class="p-5 border-t bg-white" :class="bodyBorderClass">
      <div class="lg:hidden mb-6 p-4 rounded-lg bg-slate-50 border border-slate-100">
        <p class="text-slate-700 text-sm font-medium">{{ desc }}</p>
      </div>
      
      <!-- Content (Body / Responses passed from slot) -->
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  method: String,
  path: String,
  desc: String,
});

const isOpen = ref(false);
const copied = ref(false);

const methodColor = computed(() => {
  if (props.method === 'GET') return 'bg-blue-500';
  if (props.method === 'POST') return 'bg-emerald-500';
  if (props.method === 'PUT' || props.method === 'PATCH') return 'bg-amber-500';
  if (props.method === 'DELETE') return 'bg-red-500';
  return 'bg-slate-500';
});

const containerClass = computed(() => {
  if (props.method === 'GET') return 'border-blue-200';
  if (props.method === 'POST') return 'border-emerald-200';
  if (props.method === 'PUT' || props.method === 'PATCH') return 'border-amber-200';
  if (props.method === 'DELETE') return 'border-red-200';
  return 'border-slate-200';
});

const headerClass = computed(() => {
  let base = 'hover:bg-opacity-80 transition-colors ';
  if (props.method === 'GET') return base + 'bg-blue-50';
  if (props.method === 'POST') return base + 'bg-emerald-50';
  if (props.method === 'PUT' || props.method === 'PATCH') return base + 'bg-amber-50';
  if (props.method === 'DELETE') return base + 'bg-red-50';
  return base + 'bg-slate-50';
});

const textColor = computed(() => {
  if (props.method === 'GET') return 'text-blue-900';
  if (props.method === 'POST') return 'text-emerald-900';
  if (props.method === 'PUT' || props.method === 'PATCH') return 'text-amber-900';
  if (props.method === 'DELETE') return 'text-red-900';
  return 'text-slate-900';
});

const ringClass = computed(() => {
  if (props.method === 'GET') return 'ring-blue-500';
  if (props.method === 'POST') return 'ring-emerald-500';
  if (props.method === 'PUT' || props.method === 'PATCH') return 'ring-amber-500';
  if (props.method === 'DELETE') return 'ring-red-500';
  return 'ring-slate-500';
});

const bodyBorderClass = computed(() => {
  if (props.method === 'GET') return 'border-blue-100';
  if (props.method === 'POST') return 'border-emerald-100';
  if (props.method === 'PUT' || props.method === 'PATCH') return 'border-amber-100';
  if (props.method === 'DELETE') return 'border-red-100';
  return 'border-slate-100';
});

function copyPath() {
  navigator.clipboard.writeText(props.path);
  copied.value = true;
  setTimeout(() => copied.value = false, 2000);
}
</script>
