<template>
  <AdminLayout title="سجلات النظام (Logs)">
    <div class="max-w-5xl space-y-6 pb-12">
      
      <!-- Header Area -->
      <div class="card bg-slate-900 border-0 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-red-600/20 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="bg-white/10 p-3 rounded-2xl flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold font-mono">laravel.log</h2>
              <p class="text-slate-400 mt-1 text-sm">مراقبة وتشخيص أخطاء النظام (أحدث 100 سجل)</p>
            </div>
          </div>
          <button @click="clearLogs" class="btn bg-red-600 hover:bg-red-500 text-white border-none shrink-0 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            تفريغ السجل
          </button>
        </div>
      </div>

      <!-- Controls (Filter) -->
      <div v-if="logs.length" class="flex flex-wrap gap-2 items-center">
        <span class="text-sm font-semibold text-slate-600 ml-2">تصفية حسب المستوى:</span>
        <button @click="filterLevel = null" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                :class="filterLevel === null ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
          الكل ({{ logs.length }})
        </button>
        <button @click="filterLevel = 'error'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                :class="filterLevel === 'error' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-600 hover:bg-red-200'">
          ERROR
        </button>
        <button @click="filterLevel = 'warning'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                :class="filterLevel === 'warning' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-600 hover:bg-amber-200'">
          WARNING
        </button>
        <button @click="filterLevel = 'info'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                :class="filterLevel === 'info' ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-600 hover:bg-blue-200'">
          INFO
        </button>
      </div>

      <!-- Logs Display -->
      <div v-if="!logs.length" class="card text-center py-16 bg-slate-50 border-dashed border-2">
        <div class="bg-emerald-100 text-emerald-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800">لا توجد أخطاء!</h3>
        <p class="text-slate-500 mt-2">نظامك يعمل بكفاءة ولا توجد أي مشاكل مسجلة حالياً.</p>
      </div>

      <div class="space-y-4">
        <div v-for="(log, idx) in filteredLogs" :key="idx" 
             class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200 border-r-4"
             :class="getBorderColor(log.level)">
          
          <!-- Card Header (Clickable) -->
          <div class="flex items-center justify-between p-4 bg-slate-50 cursor-pointer hover:bg-slate-100 select-none"
               @click="toggleRow(idx)">
            <div class="flex items-center gap-3">
              <span class="inline-block px-2.5 py-1 text-xs font-bold font-mono tracking-wider rounded border"
                    :class="getBadgeClass(log.level)">
                {{ String(log.level).toUpperCase() }}
              </span>
              <span class="text-slate-500 text-sm font-mono whitespace-nowrap" dir="ltr">{{ log.date }}</span>
              <span class="hidden md:inline-block text-slate-400 text-xs font-mono px-2 py-0.5 bg-white rounded border">
                {{ log.env }}
              </span>
            </div>
            
            <button class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-primary-600 hover:border-primary-300 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" 
                   :class="{'rotate-180': openRows.includes(idx)}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>

          <!-- Card Body -->
          <div v-show="!openRows.includes(idx)" class="p-4 border-t border-slate-100" @click="toggleRow(idx)">
            <p class="text-sm font-mono text-slate-700 truncate cursor-pointer" dir="ltr">{{ log.message.split('\n')[0] }}</p>
          </div>

          <!-- Extended Output (Terminal Style) -->
          <div v-show="openRows.includes(idx)" class="bg-[#1e1e1e] p-4 border-t border-slate-200">
            <pre class="text-xs font-mono text-emerald-400 leading-relaxed overflow-x-auto whitespace-pre-wrap" dir="ltr">{{ log.message }}</pre>
          </div>
        </div>
      </div>

      <!-- State: No results after filtering -->
      <div v-if="logs.length && !filteredLogs.length" class="text-center py-12 text-slate-500">
        لا توجد سجلات تطابق الفلتر المختار.
      </div>
      
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ logs: Array });
const openRows = ref([]);
const filterLevel = ref(null);

const filteredLogs = computed(() => {
  if (!filterLevel.value) return props.logs;
  
  return props.logs.filter(log => {
    const lvl = String(log.level).toLowerCase();
    if (filterLevel.value === 'error') {
      return ['error', 'critical', 'alert', 'emergency'].includes(lvl);
    }
    return lvl === filterLevel.value;
  });
});

function toggleRow(idx) {
  if (openRows.value.includes(idx)) {
    openRows.value = openRows.value.filter(i => i !== idx);
  } else {
    openRows.value.push(idx);
  }
}

function clearLogs() {
  if (confirm('الرجاء التأكيد: هل تريد فعلاً مسح جميع الأخطاء نهائياً؟')) {
    router.delete(route('admin.error-log.clear'), { preserveScroll: true });
    openRows.value = [];
  }
}

function getBadgeClass(level) {
  const lowered = String(level).toLowerCase();
  if (['error', 'critical', 'alert', 'emergency'].includes(lowered)) {
    return 'bg-red-50 text-red-600 border-red-200';
  } else if (lowered === 'warning') {
    return 'bg-amber-50 text-amber-600 border-amber-200';
  } else if (['info', 'notice'].includes(lowered)) {
    return 'bg-blue-50 text-blue-600 border-blue-200';
  }
  return 'bg-slate-50 text-slate-600 border-slate-200';
}

function getBorderColor(level) {
  const lowered = String(level).toLowerCase();
  if (['error', 'critical', 'alert', 'emergency'].includes(lowered)) {
    return 'border-r-red-500';
  } else if (lowered === 'warning') {
    return 'border-r-amber-400';
  } else if (['info', 'notice'].includes(lowered)) {
    return 'border-r-blue-500';
  }
  return 'border-r-slate-300';
}
</script>
