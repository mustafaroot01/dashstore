<template>
  <AdminLayout title="تنبيهات المخزون">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">تنبيهات المخزون</h1>
        <p class="text-slate-500 mt-2 text-sm leading-relaxed max-w-lg">
          عرض جميع المنتجات التي وصل مخزونها إلى حد التنبيه الآمن ({{ lowStockThreshold }}) أو أقل.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <!-- Search Box -->
        <div class="relative w-full md:w-80">
          <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
          <input 
            type="text" 
            v-model="search" 
            @input="doSearch"
            placeholder="بحث بالكود (SKU) أو اسم المنتج..."
            class="w-full pl-4 pr-11 py-2.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all shadow-sm font-medium text-slate-700 placeholder:text-slate-400"
          >
        </div>

        <a :href="route('admin.inventory.alerts.export', { search: search })" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200/60 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-colors shadow-sm whitespace-nowrap">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          تصدير Excel
        </a>
        
        <Link :href="route('admin.settings.index')" class="bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-800 border border-slate-200/80 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center justify-center whitespace-nowrap group">
          <svg class="w-5 h-5 ml-2 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          تعديل الحد
        </Link>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden mb-8">
      <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
          <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold text-[13px] tracking-wide">
            <tr>
              <th class="py-4 px-5">القسم</th>
              <th class="py-4 px-5">كود المنتج (SKU)</th>
              <th class="py-4 px-5">المنتج والتفاصيل</th>
              <th class="py-4 px-5 text-center">المخزون الحالي</th>
              <th class="py-4 px-5 text-center">حالة التنبيه</th>
              <th class="py-4 px-5 text-left">إجراءات</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80">
            <tr v-for="v in alerts.data" :key="v.id" class="hover:bg-slate-50/60 transition-colors group">
              <td class="py-4 px-5 text-slate-600 font-medium whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100/80 text-slate-700 border border-slate-200/60 shadow-sm">
                  {{ v.category_name }}
                </span>
              </td>
              <td class="py-4 px-5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-mono text-xs font-medium bg-slate-50/80 text-slate-600 border border-slate-200/60 uppercase tracking-wider" dir="ltr">
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                  {{ v.sku || 'N/A' }}
                </span>
              </td>
              <td class="py-4 px-5">
                <Link :href="route('admin.products.edit', v.product_id)" class="text-slate-800 font-bold hover:text-primary-600 transition-colors truncate block max-w-sm text-[15px]">
                  {{ v.product_name }}
                </Link>
                <div v-if="v.color || v.size" class="text-xs font-medium text-slate-500 mt-1.5 flex items-center gap-2 flex-wrap">
                  <span v-if="v.color" class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>{{ v.color }}
                  </span>
                  <span v-if="v.size" class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>{{ v.size }}
                  </span>
                </div>
              </td>
              <td class="py-4 px-5 text-center">
                <div class="inline-flex flex-col items-center justify-center">
                  <span class="text-2xl font-black tracking-tighter leading-none" :class="v.stock === 0 ? 'text-red-600' : 'text-orange-500'">{{ v.stock }}</span>
                </div>
              </td>
              <td class="py-4 px-5 text-center">
                <span v-if="v.stock === 0" class="px-3 py-1.5 rounded-lg text-red-700 bg-red-50 text-[11px] font-black border border-red-100 shadow-sm inline-flex items-center gap-1.5">
                   <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse relative"><span class="absolute inset-0 rounded-full bg-red-500 blur-sm"></span></span> نفذت الكمية تماماً
                </span>
                <span v-else class="px-3 py-1.5 rounded-lg text-orange-700 bg-orange-50 text-[11px] font-black border border-orange-100 shadow-sm inline-flex items-center gap-1.5">
                   <span class="w-2 h-2 rounded-full bg-orange-500"></span> مخزون قليل جداً
                </span>
              </td>
              <td class="py-4 px-5 text-left whitespace-nowrap">
                <Link :href="route('admin.products.edit', v.product_id)" class="inline-flex items-center justify-center bg-white text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2 rounded-lg text-[13px] font-bold transition-all border border-slate-200 hover:border-primary-200 shadow-sm group-hover:bg-white">
                  إدارة المنتج
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!alerts.data.length" class="text-center py-20 bg-slate-50/40">
        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-emerald-100 shadow-sm rotate-3">
          <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h3 class="text-xl font-black text-slate-800">كل شيء على ما يرام!</h3>
        <p class="text-slate-500 mt-2 max-w-sm mx-auto text-sm leading-relaxed">
          جميع منتجاتك تمتلك مخزوناً كافياً أعلى من حد الخطر الذي قمت بتحديده ({{ lowStockThreshold }} قطع).
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="alerts.links.length > 3" class="px-6 py-4 border-t border-slate-100 bg-white flex items-center justify-center">
         <div class="flex items-center gap-1.5 bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 shadow-sm">
            <Link 
              v-for="(link, i) in alerts.links" :key="i"
              :href="link.url ?? '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all min-w-[36px] text-center flex items-center justify-center"
              :class="[
                link.active ? 'bg-white text-primary-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50 border border-transparent',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
            />
         </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  alerts: Object,
  filters: Object,
  lowStockThreshold: Number,
});

const search = ref(props.filters.search ?? '');

const doSearch = debounce(() => {
  router.get(route('admin.inventory.alerts'), { search: search.value }, { preserveState: true, replace: true });
}, 500);
</script>
